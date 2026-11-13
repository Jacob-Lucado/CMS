<?php

namespace App\Services\Social;

use App\Models\Articles\WebsiteArticle;
use App\Models\Help\WebsiteHelpCenterTicket;
use App\Models\Help\WebsiteHelpCenterTicketReply;
use App\Models\Home\UserHomeMessage;
use App\Models\Home\UserHomeRating;
use App\Models\Shop\WebsiteShopPurchase;
use App\Models\Social\SocialPost;
use App\Models\User;
use App\Models\User\Referral;
use App\Models\WebsiteDrawBadge;
use Throwable;

/**
 * Turns things that happen around the website into notifications and feed posts.
 */
class SocialActivity
{
    public static function register(): void
    {
        UserHomeMessage::created(fn (UserHomeMessage $message) => self::safely(fn () => self::notifier()->send(
            $message->recipient_user_id, 'guestbook', __(':name wrote in your guestbook.', ['name' => self::name($message->user_id)]),
            self::home($message->recipient_user_id), User::query()->find($message->user_id),
        )));

        UserHomeRating::created(fn (UserHomeRating $rating) => self::safely(fn () => self::notifier()->send(
            $rating->rated_user_id, 'profile_rating', __(':name rated your home :stars.', ['name' => self::name($rating->user_id), 'stars' => str_repeat('★', max(1, min(5, (int) $rating->rating)))]),
            self::home($rating->rated_user_id), User::query()->find($rating->user_id),
        )));

        WebsiteHelpCenterTicket::created(fn (WebsiteHelpCenterTicket $ticket) => self::safely(fn () => self::notifier()->staff(
            'manage_website_tickets', 'ticket_created', __(':name opened a ticket: :title', ['name' => self::name($ticket->user_id), 'title' => $ticket->title]),
            route('help-center.ticket.show', $ticket->id, false), User::query()->find($ticket->user_id),
        )));

        WebsiteHelpCenterTicketReply::created(fn (WebsiteHelpCenterTicketReply $reply) => self::safely(function () use ($reply): void {
            $ticket = WebsiteHelpCenterTicket::query()->find($reply->ticket_id);
            if ($ticket === null) {
                return;
            }

            $url = route('help-center.ticket.show', $ticket->id, false);
            $actor = User::query()->find($reply->user_id);

            if ($ticket->user_id === $reply->user_id) {
                self::notifier()->staff('manage_website_tickets', 'ticket_staff_reply', __(':name replied to their ticket: :title', ['name' => self::name($reply->user_id), 'title' => $ticket->title]), $url, $actor);

                return;
            }

            if ($ticket->user_id !== null) {
                self::notifier()->send($ticket->user_id, 'ticket_reply', __(':name replied to your ticket.', ['name' => self::name($reply->user_id)]), $url, $actor);
            }
        }));

        WebsiteArticle::created(fn (WebsiteArticle $article) => self::safely(fn () => self::notifier()->broadcast(
            User::query()->where('last_online', '>=', now()->subDays(60)->timestamp)->pluck('id'),
            'article', __('New article: :title', ['title' => $article->title]), route('article.show', $article->slug, false),
        )));

        WebsiteShopPurchase::created(fn (WebsiteShopPurchase $purchase) => self::safely(function () use ($purchase): void {
            $package = (string) ($purchase->package->name ?? __('a package'));
            $url = route('shop.index', absolute: false);

            if ($purchase->gifted_to) {
                self::notifier()->send($purchase->gifted_to, 'gift_received', __(':name sent you :package as a gift!', ['name' => self::name($purchase->user_id), 'package' => $package]), $url);
                self::notifier()->send($purchase->user_id, 'gift_sent', __('Your gift of :package was sent to :name.', ['package' => $package, 'name' => self::name($purchase->gifted_to)]), $url);

                return;
            }

            self::notifier()->send($purchase->user_id, 'purchase', __('Thanks for buying :package!', ['package' => $package]), $url);
        }));

        WebsiteDrawBadge::created(fn (WebsiteDrawBadge $badge) => self::safely(fn () => (int) $badge->published === 1 ? null : self::notifier()->staff(
            'housekeeping_access', 'badge_submitted', __(':name made a badge that needs review: :badge', ['name' => self::name($badge->user_id), 'badge' => $badge->badge_name]), null,
        )));

        WebsiteDrawBadge::updated(fn (WebsiteDrawBadge $badge) => self::safely(fn () => $badge->wasChanged('published') && (int) $badge->published === 1 ? self::notifier()->send(
            $badge->user_id, 'badge_approved', __('Your badge ":badge" was approved!', ['badge' => $badge->badge_name]), null,
        ) : null));

        Referral::created(fn (Referral $referral) => self::safely(function () use ($referral): void {
            $newcomer = self::name($referral->referred_user_id);
            SocialPost::query()->create([
                'user_id' => $referral->user_id,
                'type' => SocialPost::REFERRAL,
                'body' => __('Invited :name to the hotel. Welcome, @:name!', ['name' => $newcomer]),
            ]);
        }));
    }

    private static function notifier(): Notifier
    {
        return app(Notifier::class);
    }

    private static function name(?int $userId): string
    {
        return (string) ($userId ? User::query()->whereKey($userId)->value('username') : null) ?: __('Someone');
    }

    private static function home(int $userId): ?string
    {
        $name = User::query()->whereKey($userId)->value('username');

        return $name ? route('home.show', $name, false) : null;
    }

    /** A notification is never worth breaking the action that caused it. */
    private static function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
