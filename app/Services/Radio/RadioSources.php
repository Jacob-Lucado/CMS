<?php

namespace App\Services\Radio;

use App\Models\Radio\RadioEntry;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\UriInterface;
use Throwable;

/**
 * Turns a pasted link into something the radio can play, or explains why it can't.
 */
class RadioSources
{
    public function __construct(private readonly PublicHost $hosts) {}

    /** @return array{kind: string, title: string, subtitle: string, url: string, stream: string, art: string|null} */
    public function fromLink(string $link, string $name = '', string $genre = ''): array
    {
        $url = $this->parse($link);

        return preg_match('/^((www|m)\.)?soundcloud\.com$|^on\.soundcloud\.com$/', $url['host']) === 1
            ? $this->soundcloud($link)
            : $this->station($link, $url['host'], $name, $genre);
    }

    /** @return array{kind: string, title: string, subtitle: string, url: string, stream: string, art: string|null} */
    private function soundcloud(string $link): array
    {
        try {
            $info = Http::timeout(10)->get('https://soundcloud.com/oembed', ['format' => 'json', 'url' => $link])->throw()->json();
        } catch (ConnectionException|RequestException) {
            $this->fail(__("SoundCloud didn't know that link. Make sure it's a public track or set."));
        }

        $player = preg_match('/src="(https:\/\/w\.soundcloud\.com\/player\/\?[^"]+)"/', (string) ($info['html'] ?? ''), $m) === 1 ? html_entity_decode($m[1]) : '';
        parse_str((string) parse_url($player, PHP_URL_QUERY), $query);
        $stream = is_string($query['url'] ?? null) ? $query['url'] : '';

        if (preg_match('/^https:\/\/api\.soundcloud\.com\/(tracks|playlists)\/\d+$/', $stream) !== 1) {
            $this->fail(__("That link can't be played here. Try another one."));
        }

        $art = (string) ($info['thumbnail_url'] ?? '');

        return [
            'kind' => RadioEntry::TRACK,
            'title' => Str::limit((string) preg_replace('/\s+by\s+.+$/i', '', (string) ($info['title'] ?? 'Untitled')), 117),
            'subtitle' => Str::limit((string) ($info['author_name'] ?? ''), 117),
            'url' => Str::limit($link, 297),
            'stream' => $stream,
            'art' => preg_match('/^https:\/\/[a-z0-9.-]*sndcdn\.com\//', $art) === 1 ? Str::limit($art, 297, '') : null,
        ];
    }

    /** @return array{kind: string, title: string, subtitle: string, url: string, stream: string, art: string|null} */
    private function station(string $link, string $host, string $name, string $genre): array
    {
        if (! $this->hosts->allows($host)) {
            $this->fail(__("That address isn't a public radio station."));
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders(['Icy-MetaData' => '1', 'User-Agent' => 'HotelRadio/1.0'])
                ->withOptions([
                    'stream' => true,
                    'allow_redirects' => [
                        'max' => 3,
                        'protocols' => ['https'],
                        'on_redirect' => function ($request, $response, UriInterface $to): void {
                            if (! $this->hosts->allows($to->getHost())) {
                                throw new TransferException('Redirected to a private address.');
                            }
                        },
                    ],
                ])
                ->get($link);
        } catch (Throwable) {
            $this->fail(__("That station didn't answer. Check the link and try again."));
        }

        $type = strtolower($response->header('Content-Type'));
        $icyName = trim($response->header('icy-name'));
        $icyGenre = trim($response->header('icy-genre'));

        try {
            $response->toPsrResponse()->getBody()->close();
        } catch (Throwable) {
            // The check is done either way.
        }

        $isAudio = str_starts_with($type, 'audio/') || str_starts_with($type, 'application/ogg') || $icyName !== '';

        if (! $response->successful() || ! $isAudio) {
            $this->fail(__("That link isn't an audio stream. Paste the station's stream address (it usually ends in .mp3, .aac or /stream)."));
        }

        $title = trim($name) !== '' ? trim($name) : ($icyName !== '' ? $icyName : $host);

        return [
            'kind' => RadioEntry::STATION,
            'title' => Str::limit(strip_tags($title), 117),
            'subtitle' => Str::limit(strip_tags(trim($genre) !== '' ? trim($genre) : $icyGenre), 117),
            'url' => Str::limit($link, 297, ''),
            'stream' => Str::limit($link, 297, ''),
            'art' => null,
        ];
    }

    /** @return array{host: string} */
    private function parse(string $link): array
    {
        $parts = parse_url(trim($link));

        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || strlen($link) > 300) {
            $this->fail(__('Paste a full https:// link to a SoundCloud track or a radio stream.'));
        }

        if (isset($parts['port']) && ! in_array((int) $parts['port'], [443, 8000, 8080, 8443], true)) {
            $this->fail(__("That address isn't a public radio station."));
        }

        return ['host' => strtolower((string) $parts['host'])];
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['link' => $message]);
    }
}
