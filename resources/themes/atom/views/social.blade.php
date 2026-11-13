<x-app-layout>
    @push('title', $profileUser ? $profileUser->username : __('Social'))

    @php
        $backgrounds = [
            asset('assets/images/profile/profile-bg.png'),
            asset('assets/images/default-background.png'),
            asset('assets/images/background.png'),
            asset('assets/images/staff-bg.png'),
            asset('assets/images/dusk/background_image.png'),
            asset('assets/images/background-dark.jpg'),
        ];
    @endphp

    <div class="col-span-12 sf" x-data="socialFeed(@js($config + ['backgrounds' => $backgrounds]))" x-init="start()">
        <section class="sf-main">
            <template x-if="owner">
                <header class="sf-banner" :style="owner.background ? `background-image:url('${owner.background}')` : ''">
                    <img class="sf-figure" :src="owner.figure" alt="">
                    <div class="sf-who">
                        <b class="sf-name" x-text="owner.name"></b>
                        <span class="sf-motto" x-text="owner.motto"></span>
                        <div class="sf-row">
                            <a class="sf-btn" :href="owner.home">🏠 {{ __('Visit home') }}</a>
                            <template x-if="owner.id === me.id">
                                <span class="sf-row">
                                    <button type="button" class="sf-btn" x-on:click="share({ type: 'profile' })">📸 {{ __('Share a snapshot') }}</button>
                                    <button type="button" class="sf-btn" x-on:click="picking = !picking">🖼️ {{ __('Background') }}</button>
                                </span>
                            </template>
                        </div>
                    </div>
                </header>
            </template>

            <div class="sf-card sf-bgpick" x-show="picking" x-cloak>
                <b>{{ __('Profile background') }}</b>
                <div class="sf-bgs">
                    <template x-for="bg in backgrounds" :key="bg">
                        <button type="button" class="sf-bg" :class="background === bg && 'on'" :style="`background-image:url('${bg}')`" x-on:click="saveBackground(bg)"></button>
                    </template>
                    <button type="button" class="sf-bg sf-nobg" x-on:click="saveBackground(null)">{{ __('None') }}</button>
                </div>
                <form class="sf-row" x-on:submit.prevent="saveBackground(customBg)">
                    <input class="sf-input" x-model="customBg" placeholder="{{ __('Or paste an https image link') }}">
                    <button class="sf-btn sf-primary">{{ __('Use it') }}</button>
                </form>
            </div>

            <template x-if="!owner || owner.id === me.id">
                <div class="sf-card sf-compose">
                    <div class="sf-tabs">
                        <template x-for="t in kinds" :key="t.id">
                            <button type="button" :class="kind === t.id && 'on'" x-on:click="pickKind(t.id)" x-text="t.label"></button>
                        </template>
                    </div>
                    <div class="sf-composebox">
                        <img class="sf-head" :src="me.head" alt="">
                        <div class="sf-grow">
                            <div class="sf-mention">
                                <textarea class="sf-input sf-text" rows="2" maxlength="1000" x-model="draft.body" x-on:input="lookup($event)" x-on:keydown.escape="suggestions = []"
                                          :placeholder="kind === 'status' ? @js(__("What's happening in the hotel? Use @name to tag someone.")) : @js(__('Say something about it (optional)'))"></textarea>
                                <ul class="sf-suggest" x-show="suggestions.length" x-cloak>
                                    <template x-for="u in suggestions" :key="u.id">
                                        <li><button type="button" x-on:click="tag(u)"><img :src="u.head" alt=""><span x-text="u.name"></span></button></li>
                                    </template>
                                </ul>
                            </div>

                            <template x-if="kind === 'video'">
                                <input class="sf-input" x-model="draft.video" placeholder="{{ __('YouTube link') }}">
                            </template>

                            <template x-if="kind === 'photo'">
                                <div>
                                    <div class="sf-row sf-small">
                                        <span class="sf-muted">{{ __('Pick up to 6 photos. Two or more make a collage.') }}</span>
                                        <label class="sf-check"><input type="checkbox" x-model="pickMine" x-on:change="loadPickable()"> {{ __('Only mine') }}</label>
                                    </div>
                                    <div class="sf-pickgrid">
                                        <template x-for="p in pickable" :key="p.id">
                                            <button type="button" class="sf-pickph" :class="draft.photos.includes(p.id) && 'on'" x-on:click="togglePhoto(p.id)"><img :src="p.url" alt="" loading="lazy"></button>
                                        </template>
                                        <p class="sf-muted" x-show="!pickable.length">{{ __('No photos yet. Take some with the in-game camera.') }}</p>
                                    </div>
                                </div>
                            </template>

                            <template x-if="kind === 'outfit'">
                                <div class="sf-row"><img class="sf-outfitprev" :src="me.figure" alt=""><span class="sf-muted">{{ __('Shares the outfit you are wearing right now.') }}</span></div>
                            </template>

                            <div class="sf-row sf-end">
                                <span class="sf-tagged">
                                    <template x-for="name in draft.tags" :key="name">
                                        <span class="sf-chip">@<span x-text="name"></span> <button type="button" x-on:click="draft.tags = draft.tags.filter((n) => n !== name)">✕</button></span>
                                    </template>
                                </span>
                                <span class="sf-msg" :class="error && 'bad'" x-text="message"></span>
                                <button type="button" class="sf-btn sf-primary" :disabled="busy" x-on:click="publish()">{{ __('Post') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="single">
                <div class="sf-row"><a class="sf-btn" :href="base">← {{ __('Back to the feed') }}</a></div>
            </template>

            <template x-for="post in posts" :key="post.id">
                <article class="sf-card sf-post">
                    <header class="sf-posthead">
                        <a :href="profileUrl(post.author)"><img class="sf-head" :src="post.author?.head" alt=""></a>
                        <div class="sf-grow">
                            <a class="sf-author" :href="profileUrl(post.author)" x-text="post.author?.name"></a>
                            <span class="sf-muted" x-text="verb(post)"></span>
                            <template x-if="post.tagged.length">
                                <span class="sf-muted">{{ __('with') }} <template x-for="(t, i) in post.tagged" :key="t.name"><span><a class="sf-link" :href="t.home" x-text="t.name"></a><span x-show="i < post.tagged.length - 1">, </span></span></template></span>
                            </template>
                            <div><a class="sf-time" :href="`${base}?post=${post.id}`" x-text="post.ago"></a></div>
                        </div>
                        <div class="sf-menu">
                            <button type="button" class="sf-iconbtn" x-on:click="post.menu = !post.menu" aria-label="{{ __('More') }}">⋯</button>
                            <div class="sf-dropdown" x-show="post.menu" x-on:click.outside="post.menu = false" x-cloak>
                                <button type="button" x-on:click="copyLink(post)">🔗 {{ __('Copy link') }}</button>
                                <button type="button" x-show="!post.mine" x-on:click="post.menu = false; post.reporting = true">🚩 {{ __('Report') }}</button>
                                <button type="button" x-show="post.can_delete" x-on:click="remove(post)">🗑️ {{ __('Delete') }}</button>
                            </div>
                        </div>
                    </header>

                    <div x-html="render(post)"></div>
                    <template x-if="post.shared">
                        <div class="sf-quote">
                            <div class="sf-row sf-small"><img class="sf-head sm" :src="post.shared.author?.head" alt=""><a class="sf-author" :href="profileUrl(post.shared.author)" x-text="post.shared.author?.name"></a><span class="sf-muted" x-text="post.shared.ago"></span></div>
                            <div x-html="render(post.shared)"></div>
                        </div>
                    </template>
                    <template x-if="post.type === 'share' && !post.shared">
                        <p class="sf-muted sf-small">{{ __('The original post was removed.') }}</p>
                    </template>

                    <form class="sf-report" x-show="post.reporting" x-cloak x-on:submit.prevent="report(post)">
                        <input class="sf-input" x-model="post.reason" maxlength="300" placeholder="{{ __("What's wrong with this post?") }}">
                        <button class="sf-btn sf-primary">{{ __('Send report') }}</button>
                        <button type="button" class="sf-btn" x-on:click="post.reporting = false">{{ __('Cancel') }}</button>
                    </form>

                    <footer class="sf-actions">
                        <button type="button" :class="post.liked && 'on'" x-on:click="like(post, 'post')">
                            <span x-text="post.liked ? '❤️' : '🤍'"></span> <span x-text="post.likes"></span>
                        </button>
                        <button type="button" x-on:click="toggleComments(post, 'post')">💬 <span x-text="post.comments"></span></button>
                        <button type="button" x-show="post.type !== 'share' || post.shared" x-on:click="share({ type: 'share', shared_post_id: post.shared?.id ?? post.id })">🔁 <span x-text="post.shares"></span></button>
                    </footer>

                    <div class="sf-comments" x-show="post.open" x-cloak>
                        <template x-for="c in post.thread" :key="c.id">
                            <div class="sf-comment">
                                <img class="sf-head sm" :src="c.author?.head" alt="">
                                <div class="sf-grow"><a class="sf-author" :href="profileUrl(c.author)" x-text="c.author?.name"></a> <span x-html="text(c.body)"></span><div class="sf-time" x-text="c.ago"></div></div>
                                <button type="button" class="sf-iconbtn" x-show="c.can_delete" x-on:click="removeComment(post, c)">✕</button>
                            </div>
                        </template>
                        <form class="sf-row" x-on:submit.prevent="addComment(post, 'post')">
                            <input class="sf-input" x-model="post.reply" maxlength="500" placeholder="{{ __('Write a comment...') }}">
                            <button class="sf-btn sf-primary">{{ __('Send') }}</button>
                        </form>
                    </div>
                </article>
            </template>

            <p class="sf-empty" x-show="loaded && !posts.length">{{ __('Nothing here yet. Be the first to post!') }}</p>
            <div class="sf-row sf-center" x-show="more"><button type="button" class="sf-btn" x-on:click="load(true)">{{ __('Load more') }}</button></div>
        </section>

        <aside class="sf-side">
            <div class="sf-card">
                <div class="sf-row"><img class="sf-head" :src="me.head" alt=""><b x-text="me.name"></b></div>
                <div class="sf-links">
                    <a :href="base">📰 {{ __('Everyone') }}</a>
                    <a :href="`${base}/u/${me.name}`">👤 {{ __('My posts') }}</a>
                    <a :href="urls.notifications">🔔 {{ __('Notifications') }}</a>
                </div>
            </div>
            <div class="sf-card" x-show="urls.camera">
                <div class="sf-row sf-between"><b>📷 {{ __('Photos') }}</b><button type="button" class="sf-btn" x-on:click="openViewer(0)" x-show="photos.length">{{ __('Watch') }} ▶</button></div>
                <div class="sf-strip">
                    <template x-for="(p, i) in photos.slice(0, 9)" :key="p.id">
                        <button type="button" x-on:click="openViewer(i)"><img :src="p.url" alt="" loading="lazy"></button>
                    </template>
                </div>
                <p class="sf-muted sf-small" x-show="!photos.length">{{ __('No photos yet.') }}</p>
            </div>
        </aside>

        <div class="sf-viewer" x-show="viewing" x-cloak x-on:keydown.escape.window="viewing = false">
            <button type="button" class="sf-close" x-on:click="viewing = false" aria-label="{{ __('Close') }}">✕</button>
            <div class="sf-reel" x-on:scroll.debounce.150ms="reelScrolled($event)">
                <template x-for="p in photos" :key="p.id">
                    <section class="sf-slide" :data-id="p.id">
                        <img :src="p.url" alt="">
                        <div class="sf-slideinfo">
                            <a class="sf-row" :href="profileUrl(p.author)"><img class="sf-head" :src="p.author?.head" alt=""><b x-text="p.author?.name"></b></a>
                            <span class="sf-muted" x-text="p.ago"></span>
                        </div>
                        <div class="sf-rail">
                            <button type="button" x-on:click="like(p, 'photo')"><span x-text="p.liked ? '❤️' : '🤍'"></span><small x-text="p.likes"></small></button>
                            <button type="button" x-on:click="toggleComments(p, 'photo')"><span>💬</span><small x-text="p.comments"></small></button>
                            <button type="button" x-on:click="share({ type: 'photo', photos: [p.id] })"><span>🔁</span><small>{{ __('Share') }}</small></button>
                        </div>
                        <div class="sf-drawer" x-show="p.open" x-cloak>
                            <div class="sf-row sf-between"><b>{{ __('Comments') }}</b><button type="button" class="sf-iconbtn" x-on:click="p.open = false">✕</button></div>
                            <div class="sf-drawerlist">
                                <template x-for="c in p.thread" :key="c.id">
                                    <div class="sf-comment">
                                        <img class="sf-head sm" :src="c.author?.head" alt="">
                                        <div class="sf-grow"><b x-text="c.author?.name"></b> <span x-html="text(c.body)"></span><div class="sf-time" x-text="c.ago"></div></div>
                                        <button type="button" class="sf-iconbtn" x-show="c.can_delete" x-on:click="removeComment(p, c)">✕</button>
                                    </div>
                                </template>
                                <p class="sf-muted" x-show="p.thread && !p.thread.length">{{ __('No comments yet.') }}</p>
                            </div>
                            <form class="sf-row" x-on:submit.prevent="addComment(p, 'photo')">
                                <input class="sf-input" x-model="p.reply" maxlength="500" placeholder="{{ __('Add a comment...') }}">
                                <button class="sf-btn sf-primary">{{ __('Send') }}</button>
                            </form>
                        </div>
                    </section>
                </template>
            </div>
        </div>

        <div class="sf-toast" x-show="toast" x-transition x-cloak x-text="toast"></div>
    </div>

    @push('javascript')
        <style>
            [x-cloak] { display: none !important; }
            .sf { display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 16px; align-items: start; }
            .sf-main, .sf-side { display: grid; gap: 12px; min-width: 0; }
            .sf-side { position: sticky; top: 12px; }
            .sf-card { background: var(--panel, rgba(127, 127, 127, .08)); border: 1px solid var(--line, rgba(127, 127, 127, .2)); border-radius: var(--radius, 14px); padding: 14px; min-width: 0; }
            .sf-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
            .sf-between { justify-content: space-between; } .sf-center { justify-content: center; } .sf-end { justify-content: flex-end; margin-top: 8px; }
            .sf-grow { flex: 1; min-width: 0; }
            .sf-muted { color: var(--muted, #8396ad); } .sf-small { font-size: 12.5px; }
            .sf-head { width: 42px; height: 42px; border-radius: 10px; background: var(--soft, rgba(127, 127, 127, .15)); object-fit: none; object-position: center 30%; flex: none; image-rendering: pixelated; }
            .sf-head.sm { width: 30px; height: 30px; object-fit: cover; }
            .sf-btn { background: var(--panel-2, rgba(127, 127, 127, .12)); border: 1px solid var(--line, rgba(127, 127, 127, .25)); color: var(--text, inherit); border-radius: 9px; padding: 6px 12px; font: inherit; font-size: 13px; cursor: pointer; text-decoration: none; }
            .sf-btn:hover { border-color: var(--accent, #4da3ff); }
            .sf-primary { background: var(--accent, #4da3ff); border-color: var(--accent, #4da3ff); color: var(--on-accent, #06121f); font-weight: 600; }
            .sf-btn:disabled { opacity: .6; cursor: wait; }
            .sf-input { width: 100%; min-width: 0; flex: 1; background: var(--input-bg, transparent); color: var(--text, inherit); border: 1px solid var(--line, rgba(127, 127, 127, .3)); border-radius: 8px; padding: 7px 10px; font: inherit; font-size: 13px; }
            .sf-text { resize: vertical; min-height: 54px; }
            .sf-tabs { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 10px; }
            .sf-tabs button { background: none; border: 1px solid transparent; color: var(--muted, #8396ad); border-radius: 999px; padding: 4px 11px; font: inherit; font-size: 12.5px; cursor: pointer; }
            .sf-tabs button.on { border-color: var(--accent, #4da3ff); color: var(--white, inherit); background: var(--hover-row, rgba(77, 163, 255, .08)); }
            .sf-composebox { display: flex; gap: 10px; align-items: flex-start; }
            .sf-composebox .sf-grow { display: grid; gap: 8px; }
            .sf-mention { position: relative; }
            .sf-suggest { position: absolute; z-index: 20; left: 0; top: 100%; margin: 4px 0 0; padding: 4px; list-style: none; min-width: 200px; background: var(--panel-solid, #16202c); border: 1px solid var(--line, rgba(127, 127, 127, .3)); border-radius: 10px; box-shadow: 0 10px 30px rgba(0, 0, 0, .35); }
            .sf-suggest button { display: flex; gap: 8px; align-items: center; width: 100%; background: none; border: 0; color: inherit; font: inherit; padding: 4px 6px; border-radius: 6px; cursor: pointer; }
            .sf-suggest button:hover { background: var(--hover-row, rgba(77, 163, 255, .12)); }
            .sf-suggest img { width: 28px; height: 28px; object-fit: none; }
            .sf-chip { background: var(--hover-row, rgba(77, 163, 255, .12)); border-radius: 999px; padding: 2px 8px; font-size: 12px; margin-right: 4px; }
            .sf-chip button { background: none; border: 0; color: inherit; cursor: pointer; }
            .sf-tagged { flex: 1; }
            .sf-msg { color: var(--good, #3ddc97); font-size: 13px; } .sf-msg.bad { color: var(--bad, #ff6b6b); }
            .sf-check { display: inline-flex; gap: 4px; align-items: center; margin-left: auto; font-size: 12.5px; }
            .sf-pickgrid { display: grid; grid-template-columns: repeat(auto-fill, minmax(84px, 1fr)); gap: 6px; max-height: 220px; overflow-y: auto; margin-top: 6px; }
            .sf-pickph { padding: 0; border: 2px solid transparent; border-radius: 8px; overflow: hidden; cursor: pointer; background: none; aspect-ratio: 1; }
            .sf-pickph.on { border-color: var(--accent, #4da3ff); }
            .sf-pickph img { width: 100%; height: 100%; object-fit: cover; display: block; }
            .sf-outfitprev { height: 110px; image-rendering: pixelated; }
            .sf-posthead { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 8px; }
            .sf-author { font-weight: 700; color: var(--white, inherit); text-decoration: none; }
            .sf-link { color: var(--accent, #4da3ff); text-decoration: none; }
            .sf-time { font-size: 11.5px; color: var(--muted, #8396ad); text-decoration: none; }
            .sf-body { white-space: pre-wrap; overflow-wrap: anywhere; margin: 4px 0 8px; }
            .sf-collage { display: grid; gap: 4px; border-radius: 10px; overflow: hidden; margin-bottom: 8px; }
            .sf-collage.n1 { grid-template-columns: 1fr; } .sf-collage.n2, .sf-collage.n4 { grid-template-columns: 1fr 1fr; } .sf-collage.n3, .sf-collage.n5, .sf-collage.n6 { grid-template-columns: 1fr 1fr 1fr; }
            .sf-collage img { width: 100%; aspect-ratio: 1.6; object-fit: cover; display: block; cursor: zoom-in; background: #000; }
            .sf-collage.n1 img { aspect-ratio: auto; max-height: 420px; object-fit: contain; }
            .sf-video { position: relative; aspect-ratio: 16 / 9; border-radius: 10px; overflow: hidden; background: #000; margin-bottom: 8px; }
            .sf-video iframe, .sf-video img { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; object-fit: cover; }
            .sf-video button { position: absolute; inset: 0; background: rgba(0, 0, 0, .25); border: 0; color: #fff; font-size: 54px; cursor: pointer; }
            .sf-outfit, .sf-profilecard { display: flex; gap: 14px; align-items: center; padding: 10px; border-radius: 10px; background: var(--soft, rgba(127, 127, 127, .1)); margin-bottom: 8px; }
            .sf-trophy { font-size: 34px; }
            .sf-outfit img, .sf-profilecard img { height: 130px; image-rendering: pixelated; }
            .sf-quote { border: 1px solid var(--line, rgba(127, 127, 127, .25)); border-radius: 10px; padding: 10px; margin-bottom: 8px; }
            .sf-actions { display: flex; gap: 6px; border-top: 1px solid var(--line, rgba(127, 127, 127, .2)); padding-top: 8px; }
            .sf-actions button { background: none; border: 0; color: var(--muted, #8396ad); font: inherit; font-size: 13px; padding: 5px 10px; border-radius: 8px; cursor: pointer; }
            .sf-actions button:hover, .sf-actions button.on { background: var(--hover-row, rgba(77, 163, 255, .1)); color: var(--white, inherit); }
            .sf-comments { display: grid; gap: 8px; margin-top: 8px; }
            .sf-comment { display: flex; gap: 8px; align-items: flex-start; font-size: 13px; }
            .sf-iconbtn { background: none; border: 0; color: var(--muted, #8396ad); cursor: pointer; padding: 2px 6px; border-radius: 6px; font-size: 15px; }
            .sf-iconbtn:hover { color: var(--white, inherit); background: var(--hover-row, rgba(77, 163, 255, .1)); }
            .sf-menu { position: relative; }
            .sf-dropdown { position: absolute; right: 0; top: 100%; z-index: 15; display: grid; min-width: 150px; padding: 4px; background: var(--panel-solid, #16202c); border: 1px solid var(--line, rgba(127, 127, 127, .3)); border-radius: 10px; box-shadow: 0 10px 30px rgba(0, 0, 0, .35); }
            .sf-dropdown button { text-align: left; background: none; border: 0; color: inherit; font: inherit; font-size: 13px; padding: 6px 8px; border-radius: 6px; cursor: pointer; }
            .sf-dropdown button:hover { background: var(--hover-row, rgba(77, 163, 255, .12)); }
            .sf-report { display: flex; gap: 6px; margin-bottom: 8px; }
            .sf-empty { text-align: center; color: var(--muted, #8396ad); padding: 30px; }
            .sf-banner { position: relative; display: flex; gap: 16px; align-items: flex-end; min-height: 170px; padding: 16px; border-radius: var(--radius, 14px); background: linear-gradient(135deg, #1d3557, #457b9d) center / cover; overflow: hidden; border: 1px solid var(--line, rgba(127, 127, 127, .2)); }
            .sf-banner::before { content: ''; position: absolute; inset: 0; background: linear-gradient(transparent 30%, rgba(0, 0, 0, .65)); }
            .sf-banner > * { position: relative; }
            .sf-figure { height: 140px; image-rendering: pixelated; margin-bottom: -16px; }
            .sf-who { display: grid; gap: 4px; color: #fff; }
            .sf-name { font-size: 22px; } .sf-motto { opacity: .85; }
            .sf-bgs { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 6px; margin: 8px 0; }
            .sf-bg { aspect-ratio: 16 / 9; border-radius: 8px; border: 2px solid transparent; background: center / cover; cursor: pointer; }
            .sf-bg.on { border-color: var(--accent, #4da3ff); }
            .sf-nobg { background: var(--soft, rgba(127, 127, 127, .15)); color: inherit; font: inherit; font-size: 12px; }
            .sf-links { display: grid; gap: 2px; margin-top: 8px; }
            .sf-links a { padding: 6px 8px; border-radius: 8px; color: inherit; text-decoration: none; font-size: 13.5px; }
            .sf-links a:hover { background: var(--hover-row, rgba(77, 163, 255, .1)); }
            .sf-strip { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; margin-top: 8px; }
            .sf-strip button { padding: 0; border: 0; border-radius: 6px; overflow: hidden; aspect-ratio: 1; cursor: pointer; background: #000; }
            .sf-strip img { width: 100%; height: 100%; object-fit: cover; display: block; }
            .sf-viewer { position: fixed; inset: 0; z-index: 9000; background: #000; }
            .sf-close { position: absolute; top: 12px; right: 14px; z-index: 3; background: rgba(255, 255, 255, .15); border: 0; color: #fff; width: 38px; height: 38px; border-radius: 50%; font-size: 18px; cursor: pointer; }
            .sf-reel { height: 100%; overflow-y: auto; scroll-snap-type: y mandatory; scrollbar-width: none; }
            .sf-slide { position: relative; height: 100%; scroll-snap-align: start; display: grid; place-items: center; }
            .sf-slide > img { max-width: 100%; max-height: 100%; object-fit: contain; }
            .sf-slideinfo { position: absolute; left: 16px; bottom: 22px; color: #fff; display: grid; gap: 4px; text-shadow: 0 1px 3px #000; }
            .sf-slideinfo a { color: #fff; text-decoration: none; }
            .sf-rail { position: absolute; right: 14px; bottom: 90px; display: grid; gap: 16px; }
            .sf-rail button { display: grid; justify-items: center; gap: 2px; background: none; border: 0; color: #fff; font-size: 28px; cursor: pointer; text-shadow: 0 1px 3px #000; }
            .sf-rail small { font-size: 12px; }
            .sf-drawer { position: absolute; right: 0; bottom: 0; width: min(380px, 100%); height: 60%; display: grid; grid-template-rows: auto 1fr auto; gap: 8px; padding: 12px; background: var(--panel-solid, #16202c); color: var(--text, #e8eef5); border-radius: 16px 16px 0 0; }
            .sf-drawerlist { overflow-y: auto; display: grid; gap: 8px; align-content: start; }
            .sf-toast { position: fixed; left: 50%; bottom: 70px; transform: translateX(-50%); z-index: 9100; background: var(--panel-solid, #16202c); color: var(--text, #e8eef5); border: 1px solid var(--line, rgba(127, 127, 127, .3)); padding: 8px 16px; border-radius: 999px; box-shadow: 0 10px 30px rgba(0, 0, 0, .35); }
            @media (max-width: 860px) { .sf { grid-template-columns: 1fr; } .sf-side { position: static; order: -1; } }
        </style>
        <script>
            (() => {
                const T = {
                    status: @js(__('Status')), photo: @js(__('Photos')), video: @js(__('YouTube')), outfit: @js(__('Outfit')),
                    verbs: {
                        status: '', photo: @js(__('shared a photo')), collage: @js(__('shared a collage')), video: @js(__('shared a video')),
                        outfit: @js(__('is showing off an outfit')), profile: @js(__('shared their profile')), share: @js(__('shared a post')),
                        referral: @js(__('brought a friend to the hotel')), listing: @js(__('is selling something')),
                    },
                    visit: @js(__('Visit home')), vote: @js(__('Vote in Outfit of the Week')), posted: @js(__('Posted!')), shared: @js(__('Shared to your feed!')), copied: @js(__('Link copied.')),
                    reported: @js(__('Thanks. Staff will take a look.')), sure: @js(__('Delete this post?')), oops: @js(__('Something went wrong. Try again.')),
                };
                const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
                const social = (urls) => ({
                    urls, base: urls.base, me: urls.me, owner: urls.owner, background: urls.background, backgrounds: urls.backgrounds,
                    kinds: [{ id: 'status', label: '💬 ' + T.status }, ...(urls.camera ? [{ id: 'photo', label: '📷 ' + T.photo }] : []), { id: 'video', label: '▶️ ' + T.video }, { id: 'outfit', label: '👕 ' + T.outfit }],
                    kind: 'status', draft: { body: '', video: '', photos: [], tags: [] },
                    posts: [], more: false, loaded: false, single: false, busy: false, message: '', error: false, toast: '',
                    photos: [], pickable: [], pickMine: true, viewing: false, picking: false, customBg: '',
                    suggestions: [], mentionAt: -1,
                    start() {
                        this.box = this.$el;
                        const params = new URLSearchParams(location.search);
                        this.single = params.has('post');
                        this.load(false, params.get('post'));
                        this.loadPhotos(params.get('photo'));
                    },
                    async call(url, method = 'GET', body = null) {
                        const res = await fetch(url, {
                            method, credentials: 'same-origin',
                            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                            body: body ? JSON.stringify(body) : null,
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.errors ? Object.values(data.errors)[0][0] : (data.message || T.oops));
                        return data;
                    },
                    q(params) { return '?' + new URLSearchParams(Object.entries(params).filter(([, v]) => v !== null && v !== undefined && v !== '')).toString(); },
                    async load(next = false, postId = null) {
                        const before = next && this.posts.length ? this.posts[this.posts.length - 1].id : null;
                        try {
                            const data = await this.call(urls.feed + this.q({ before, user: this.owner?.id, post: postId }));
                            const fresh = data.posts.map((p) => this.prep(p));
                            this.posts = next ? [...this.posts, ...fresh] : fresh;
                            this.more = data.more && !postId;
                            if (postId && this.posts[0]) this.toggleComments(this.posts[0], 'post');
                        } catch (e) { this.flash(e.message); }
                        this.loaded = true;
                    },
                    prep(p) { return Object.assign(p, { open: false, thread: null, reply: '', menu: false, reporting: false, reason: '' }); },
                    async loadPhotos(openId = null) {
                        try {
                            const data = await this.call(urls.photos + this.q({ id: openId }));
                            this.photos = data.photos.map((p) => Object.assign(p, { open: false, thread: null, reply: '' }));
                            if (openId && this.photos.length) this.openViewer(0);
                        } catch { /* the strip just stays empty */ }
                    },
                    async loadPickable() {
                        try { this.pickable = (await this.call(urls.photos + this.q({ mine: this.pickMine ? 1 : 0 }))).photos; } catch { this.pickable = []; }
                    },
                    pickKind(kind) {
                        this.kind = kind;
                        this.message = '';
                        if (kind === 'photo' && !this.pickable.length) this.loadPickable();
                    },
                    togglePhoto(id) {
                        const list = this.draft.photos;
                        this.draft.photos = list.includes(id) ? list.filter((x) => x !== id) : (list.length < 6 ? [...list, id] : list);
                    },
                    async lookup(event) {
                        const el = event.target;
                        const before = el.value.slice(0, el.selectionStart);
                        const m = before.match(/(^|\s)@([\w\-.=:,!?]{1,25})$/u);
                        if (!m) { this.suggestions = []; return; }
                        this.mentionAt = before.length - m[2].length - 1;
                        try { this.suggestions = (await this.call(urls.users + this.q({ q: m[2] }))).users.filter((u) => u.id !== this.me.id); } catch { this.suggestions = []; }
                    },
                    tag(user) {
                        const body = this.draft.body;
                        const end = body.indexOf(' ', this.mentionAt);
                        this.draft.body = body.slice(0, this.mentionAt) + '@' + user.name + ' ' + (end === -1 ? '' : body.slice(end + 1));
                        if (!this.draft.tags.includes(user.name)) this.draft.tags.push(user.name);
                        this.suggestions = [];
                    },
                    async publish() {
                        this.busy = true; this.message = ''; this.error = false;
                        try {
                            const data = await this.call(urls.post, 'POST', { type: this.kind, ...this.draft });
                            this.posts.unshift(this.prep(data.post));
                            this.draft = { body: '', video: '', photos: [], tags: [] };
                            this.message = T.posted;
                        } catch (e) { this.error = true; this.message = e.message; } finally { this.busy = false; }
                    },
                    async share(body) {
                        try {
                            const data = await this.call(urls.post, 'POST', body);
                            if (!this.owner || this.owner.id === this.me.id) this.posts.unshift(this.prep(data.post));
                            const original = this.posts.find((p) => p.id === body.shared_post_id);
                            if (original) original.shares++;
                            this.flash(T.shared);
                        } catch (e) { this.flash(e.message); }
                    },
                    async like(item, type) {
                        try { Object.assign(item, await this.call(`${this.base}/like`, 'POST', { target_type: type, target_id: item.id })); } catch (e) { this.flash(e.message); }
                    },
                    async toggleComments(item, type) {
                        item.open = !item.open;
                        if (item.open && item.thread === null) {
                            try { item.thread = (await this.call(`${this.base}/comments` + this.q({ target_type: type, target_id: item.id }))).comments; } catch { item.thread = []; }
                        }
                    },
                    async addComment(item, type) {
                        if (!item.reply.trim()) return;
                        try {
                            const data = await this.call(`${this.base}/comments`, 'POST', { target_type: type, target_id: item.id, body: item.reply });
                            (item.thread ??= []).push(data.comment);
                            item.comments++;
                            item.reply = '';
                        } catch (e) { this.flash(e.message); }
                    },
                    async removeComment(item, comment) {
                        try {
                            await this.call(`${this.base}/comments/${comment.id}`, 'DELETE');
                            item.thread = item.thread.filter((c) => c.id !== comment.id);
                            item.comments--;
                        } catch (e) { this.flash(e.message); }
                    },
                    async remove(post) {
                        post.menu = false;
                        if (!confirm(T.sure)) return;
                        try { await this.call(`${this.base}/posts/${post.id}`, 'DELETE'); this.posts = this.posts.filter((p) => p.id !== post.id); } catch (e) { this.flash(e.message); }
                    },
                    async report(post) {
                        try { await this.call(`${this.base}/posts/${post.id}/report`, 'POST', { reason: post.reason }); post.reporting = false; this.flash(T.reported); } catch (e) { this.flash(e.message); }
                    },
                    copyLink(post) {
                        post.menu = false;
                        navigator.clipboard?.writeText(`${location.origin}${this.base}?post=${post.id}`).then(() => this.flash(T.copied)).catch(() => {});
                    },
                    async saveBackground(bg) {
                        try {
                            const data = await this.call(urls.profile, 'POST', { background: bg || null });
                            this.background = data.background;
                            if (this.owner) this.owner.background = data.background;
                            this.customBg = '';
                        } catch (e) { this.flash(e.message); }
                    },
                    openViewer(index) {
                        this.viewing = true;
                        this.$nextTick(() => this.box.querySelectorAll('.sf-slide')[index]?.scrollIntoView());
                    },
                    async reelScrolled(event) {
                        const reel = event.target;
                        if (reel.scrollTop + reel.clientHeight * 2 < reel.scrollHeight || this.loadingMore) return;
                        this.loadingMore = true;
                        try {
                            const data = await this.call(urls.photos + this.q({ offset: this.photos.length }));
                            this.photos.push(...data.photos.map((p) => Object.assign(p, { open: false, thread: null, reply: '' })));
                        } catch { /* stays as is */ }
                        this.loadingMore = false;
                    },
                    profileUrl(author) { return author ? `${this.base}/u/${encodeURIComponent(author.name)}` : '#'; },
                    verb(post) {
                        if (post.type === 'photo') return (post.data.photos?.length ?? 0) > 1 ? T.verbs.collage : T.verbs.photo;
                        return T.verbs[post.type] ?? '';
                    },
                    text(body) {
                        return esc(body).replace(/(^|\s)@([\w\-.=:,!?]{1,25})/gu, (all, space, name) => `${space}<a class="sf-link" href="${this.base}/u/${encodeURIComponent(name.replace(/[.,!?:]+$/, ''))}">@${name}</a>`);
                    },
                    render(post) {
                        const d = post.data || {};
                        let html = post.body ? `<p class="sf-body">${this.text(post.body)}</p>` : '';
                        if (post.type === 'photo' && d.photos) {
                            html += `<div class="sf-collage n${Math.min(d.photos.length, 6)}">${d.photos.map((p) => `<a href="${this.base}?photo=${p.id}"><img src="${esc(p.url)}" alt="" loading="lazy"></a>`).join('')}</div>`;
                        }
                        if (post.type === 'video' && d.video) {
                            const id = esc(d.video);
                            html += `<div class="sf-video"><img src="https://i.ytimg.com/vi/${id}/hqdefault.jpg" alt="" loading="lazy"><button type="button" aria-label="Play" onclick="this.parentElement.innerHTML='<iframe src=&quot;https://www.youtube-nocookie.com/embed/${id}?autoplay=1&quot; allow=&quot;autoplay; encrypted-media; picture-in-picture&quot; allowfullscreen></iframe>'">▶</button></div>`;
                        }
                        if (post.type === 'outfit' && d.look) {
                            const src = d.avatar ? urls.imager + encodeURIComponent(d.look) + '&' + d.avatar : this.imager(d.look);
                            const vote = d.outfit_id && !d.winner ? `<a class="sf-btn" href="${urls.outfits}">👕 ${T.vote}</a>` : '';
                            html += `<div class="sf-outfit"><img src="${esc(src)}" alt="">${d.winner ? '<span class="sf-trophy">🏆</span>' : ''}${vote}</div>`;
                        }
                        if (post.type === 'profile') {
                            html += `<div class="sf-profilecard"><img src="${esc(this.imager(d.look ?? ''))}" alt=""><div><b>${esc(post.author?.name)}</b><p class="sf-muted">${esc(d.motto ?? '')}</p><a class="sf-btn" href="${esc(d.home ?? post.author?.home)}">🏠 ${T.visit}</a></div></div>`;
                        }
                        return html;
                    },
                    imager(look) { return urls.imager + encodeURIComponent(look) + '&direction=2&head_direction=3&gesture=sml&size=l'; },
                    flash(text) { this.toast = text; clearTimeout(this.toastTimer); this.toastTimer = setTimeout(() => (this.toast = ''), 2600); },
                });
                if (window.Alpine) window.Alpine.data('socialFeed', social);
                else document.addEventListener('alpine:init', () => window.Alpine.data('socialFeed', social));
            })();
        </script>
    @endpush
</x-app-layout>
