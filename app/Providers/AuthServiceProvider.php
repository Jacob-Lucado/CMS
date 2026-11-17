<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Articles\WebsiteArticleComment;
use App\Models\Help\WebsiteHelpCenterTicket;
use App\Models\Help\WebsiteHelpCenterTicketReply;
use App\Models\WebsiteEvent;
use App\Policies\ActivityPolicy;
use App\Policies\WebsiteEventPolicy;
use App\Models\Help\WebsiteRuleCategory;
use App\Policies\ContentManagerPolicy;
use App\Models\Help\WebsiteRule;
use App\Models\Community\RareValue\WebsiteRareValueCategory;
use App\Models\Community\RareValue\WebsiteRareValue;
use App\Models\Miscellaneous\WebsiteBetaCode;
use App\Policies\SecurityManagerPolicy;
use App\Models\Miscellaneous\WebsiteIpBlacklist;
use App\Models\Miscellaneous\WebsiteIpWhitelist;
use App\Models\Shop\WebsiteSquarePayment;
use App\Policies\PaymentRecordPolicy;
use App\Models\Shop\WebsitePaypalTransaction;
use App\Models\Shop\WebsiteShopPurchase;
use App\Models\User\Referral;
use App\Policies\WebsiteArticleCommentPolicy;
use App\Policies\WebsiteHelpCenterTicketPolicy;
use App\Policies\WebsiteHelpCenterTicketReplyPolicy;
use App\Support\FrontendUrls;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Session\Middleware\AuthenticateSession;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        WebsiteEvent::class => WebsiteEventPolicy::class,
        WebsiteArticleComment::class => WebsiteArticleCommentPolicy::class,
        WebsiteHelpCenterTicket::class => WebsiteHelpCenterTicketPolicy::class,
        WebsiteHelpCenterTicketReply::class => WebsiteHelpCenterTicketReplyPolicy::class,
        \App\Models\Help\WebsiteRuleCategory::class => ContentManagerPolicy::class,
        \App\Models\Help\WebsiteRule::class => ContentManagerPolicy::class,
        \App\Models\Community\RareValue\WebsiteRareValueCategory::class => ContentManagerPolicy::class,
        \App\Models\Community\RareValue\WebsiteRareValue::class => ContentManagerPolicy::class,
        \App\Models\Miscellaneous\WebsiteBetaCode::class => SecurityManagerPolicy::class,
        \App\Models\Miscellaneous\WebsiteIpBlacklist::class => SecurityManagerPolicy::class,
        \App\Models\Miscellaneous\WebsiteIpWhitelist::class => SecurityManagerPolicy::class,
        \App\Models\BanAppeal::class => SecurityManagerPolicy::class,
        \App\Models\TradeLog::class => SecurityManagerPolicy::class,
        \App\Models\FeaturedRoom::class => WebsiteEventPolicy::class,
        \App\Models\Shop\WebsiteSquarePayment::class => PaymentRecordPolicy::class,
        \App\Models\Shop\WebsitePaypalTransaction::class => PaymentRecordPolicy::class,
        \App\Models\Shop\WebsiteShopPurchase::class => PaymentRecordPolicy::class,
        \App\Models\User\Referral::class => PaymentRecordPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        AuthenticateSession::redirectUsing(fn (): string => app(FrontendUrls::class)->route('login'));
    }
}
