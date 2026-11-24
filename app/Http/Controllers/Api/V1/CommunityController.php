<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Community\SubmitStaffApplication;
use App\Data\PublicUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SearchUsersRequest;
use App\Http\Requests\StaffApplicationFormRequest;
use App\Http\Resources\Api\V1\PublicUserResource;
use App\Models\Community\Staff\WebsiteOpenPosition;
use App\Models\User;
use App\Services\Community\CameraService;
use App\Services\Community\CommunityReadService;
use App\Services\Community\MusicChartsService;
use App\Services\Community\PublicRoomsService;
use App\Services\Community\RecentRoomsService;
use App\Services\Community\StaffService;
use App\Services\Community\TeamService;
use App\Services\Community\WeeklyLeaderboardService;
use App\Services\User\UserApiService;
use App\Support\AuthenticatedUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class CommunityController extends Controller
{
    public function online(UserApiService $users): AnonymousResourceCollection
    {
        return PublicUserResource::collection($users->onlineUsers(['id', 'username', 'motto', 'look', 'online'])->map(fn (User $user) => PublicUserData::from($user)));
    }

    public function users(SearchUsersRequest $request, UserApiService $users): JsonResponse
    {
        return response()->json(['data' => $users->searchUsers($request->string('q')->toString())]);
    }

    public function user(User $user): PublicUserResource
    {
        return new PublicUserResource(PublicUserData::from($user));
    }

    public function staff(Request $request, StaffService $staff): JsonResponse
    {
        return response()->json(['data' => $staff->fetchStaffPositions(AuthenticatedUser::from($request))->map(fn ($position): array => [
            'id' => $position->id, 'name' => $position->rank_name, 'badge' => $position->badge, 'color' => $position->staff_color, 'background' => $position->staff_background, 'description' => $position->job_description,
            'users' => PublicUserResource::collection($position->getRelation('users')->map(fn (User $user) => PublicUserData::from($user))),
        ])]);
    }

    public function teams(TeamService $teams): JsonResponse
    {
        return response()->json(['data' => $teams->fetchTeams()->map(fn ($team): array => [
            'id' => $team->id, 'name' => $team->rank_name, 'badge' => $team->badge, 'color' => $team->staff_color, 'background' => $team->staff_background, 'description' => $team->job_description,
            'users' => PublicUserResource::collection($team->users->map(fn (User $user) => PublicUserData::from($user))),
        ])]);
    }

    public function music(MusicChartsService $charts): JsonResponse
    {
        $data = $charts->charts();

        return response()->json(['data' => [
            'days' => MusicChartsService::DAYS,
            'songs' => collect($data['songs'])->map(fn ($song): array => ['url' => $song->url, 'kind' => $song->kind, 'plays' => (int) $song->plays])->values(),
            'djs' => collect($data['djs'])->map(fn ($dj): array => ['name' => $dj->set_by, 'plays' => (int) $dj->plays])->values(),
            'recent' => collect($data['recent'])->map(fn ($play): array => ['url' => $play->url, 'kind' => $play->kind, 'set_by' => $play->set_by, 'room' => $play->getAttribute('room_name'), 'played_at' => $play->played_at->toIso8601String()])->values(),
        ]]);
    }

    public function publicRooms(PublicRoomsService $publicRooms): JsonResponse
    {
        return response()->json(['data' => $publicRooms->rooms()]);
    }

    public function recentRooms(Request $request, RecentRoomsService $recent): JsonResponse
    {
        $user = $request->user();

        return response()->json(['data' => $user instanceof User ? $recent->forUser($user) : []]);
    }

    public function leaderboards(CommunityReadService $community): JsonResponse
    {
        return response()->json(['data' => collect($community->leaderboards())->map(fn ($entries) => $entries?->map(fn ($entry): array => ['user' => new PublicUserResource(PublicUserData::from($entry->user)), 'value' => $entry->value]))]);
    }

    public function weeklyLeaderboards(WeeklyLeaderboardService $weekly): JsonResponse
    {
        $board = $weekly->current();
        $entries = fn ($rows) => $rows->map(fn ($entry): array => ['user' => new PublicUserResource(PublicUserData::from($entry->user)), 'value' => $entry->value])->values();

        return response()->json(['data' => [
            'week_start' => $weekly->weekStart()->toDateString(),
            'music' => $entries($board['weeklyMusic']),
            'kudos' => $entries($board['weeklyKudos']),
            'last_winners' => $board['lastWinners']->flatMap(fn ($winner): array => $winner->user === null ? [] : [[
                'board' => $winner->board,
                'week_start' => $winner->week_start->toDateString(),
                'score' => $winner->score,
                'user' => new PublicUserResource(PublicUserData::from($winner->user)),
            ]])->values(),
        ]]);
    }

    public function photos(CameraService $camera): AnonymousResourceCollection
    {
        return JsonResource::collection($camera->fetchPhotos(true)->through(fn ($photo): array => ['id' => $photo->id, 'url' => url($photo->url), 'created_at' => $photo->timestamp->toIso8601String(), 'author' => $photo->user ? new PublicUserResource(PublicUserData::from($photo->user)) : null]));
    }

    public function applications(Request $request, CommunityReadService $community): JsonResponse
    {
        $data = $request->validate(['kind' => ['sometimes', 'in:rank,team']]);
        $positions = isset($data['kind']) ? $community->positions($data['kind']) : $community->positions('rank')->concat($community->positions('team'));

        $statuses = $community->teamApplicationStatuses(AuthenticatedUser::from($request), $positions->pluck('team_id')->filter()->unique()->all());

        return response()->json(['data' => $positions->map(fn ($position): array => $this->positionData($position, $statuses))->values()]);
    }

    public function position(WebsiteOpenPosition $position, Request $request, CommunityReadService $community): JsonResponse
    {
        $position = $community->position($position);
        $statuses = $community->teamApplicationStatuses(AuthenticatedUser::from($request), $position->team_id !== null ? [$position->team_id] : []);

        return response()->json(['data' => $this->positionData($position, $statuses)]);
    }

    public function apply(WebsiteOpenPosition $position, StaffApplicationFormRequest $request, CommunityReadService $community, SubmitStaffApplication $applications): Response
    {
        $applications->forPosition(AuthenticatedUser::from($request), $position, $request->validated('content'));

        return response()->noContent(201);
    }

    /**
     * @param  array<int, string>  $statuses
     *
     * @return array<string, mixed>
     */
    private function positionData(WebsiteOpenPosition $position, array $statuses = []): array
    {
        $role = $position->position_kind === 'team' ? $position->team : $position->permission;
        $applicationStatus = $position->position_kind === 'team' && $position->team_id !== null ? ($statuses[$position->team_id] ?? null) : null;

        return ['id' => $position->id, 'application_status' => $applicationStatus, 'kind' => $position->position_kind, 'name' => $role?->rank_name, 'badge' => $role?->badge, 'color' => $role?->staff_color, 'description' => $position->description, 'group_description' => $role?->job_description, 'apply_from' => $position->apply_from?->toIso8601String(), 'apply_to' => $position->apply_to?->toIso8601String()];
    }
}
