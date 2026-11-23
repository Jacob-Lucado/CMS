<?php

namespace App\Http\Controllers\Community;

use App\Data\PublicUserData;
use App\Emulator\Contracts\PhotoRepository;
use App\Emulator\Data\Feature;
use App\Emulator\Data\Photo;
use App\Emulator\Emulator;
use App\Http\Controllers\Controller;
use App\Http\Responses\AccessResponse;
use App\Models\User;
use App\Services\Community\CameraService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Response;

class PhotosController extends Controller
{
    private const PER_PAGE = 8;

    public function __construct(private readonly CameraService $cameraService, private readonly PhotoRepository $photoRepository) {}

    public function __invoke(Request $request): Response
    {
        if (Emulator::supports(Feature::CameraPhotos)) {
            return response()->view('community.photos', ['photos' => $this->cameraService->fetchPhotos(true)]);
        }

        if (! $this->photoRepository->available()) {
            return AccessResponse::make($request, 'feature_unavailable', 'This feature is not available on this hotel.', 404, 'welcome', true);
        }

        return response()->view('community.photos', ['photos' => $this->published($request)]);
    }

    /** Emulators that keep their own photo tables list them through the repository. */
    private function published(Request $request): Paginator
    {
        $page = max(1, (int) $request->query('page', 1));
        $photos = $this->photoRepository->latest(($page - 1) * self::PER_PAGE, self::PER_PAGE + 1);
        $users = User::query()->whereIn('id', $photos->pluck('userId')->unique())->get(PublicUserData::COLUMNS)->keyBy('id');

        $items = $photos->map(fn (Photo $photo): object => (object) ['url' => $photo->url, 'user' => $users->get($photo->userId)]);

        return (new Paginator($items->values(), self::PER_PAGE, $page, ['path' => $request->url()]))->hasMorePagesWhen($items->count() > self::PER_PAGE);
    }
}
