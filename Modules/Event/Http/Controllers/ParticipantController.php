<?php


namespace Modules\Event\Http\Controllers;


use App\Config\Csv;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Modules\Event\Repositories\EventRepository;
use Modules\Event\Services\ParticipantExportService;

/**
 * Class ParticipantController
 * @package Modules\Event\Http\Controllers
 */
class ParticipantController
{
    /** @var EventRepository $eventRepository */
    private $eventRepository;

    /** @var ParticipantExportService $participantExportService */
    private $participantExportService;

    /**
     * ParticipantController constructor.
     * @param EventRepository $eventRepository
     * @param ParticipantExportService $participantExportService
     */
    public function __construct(EventRepository $eventRepository, ParticipantExportService $participantExportService)
    {
        $this->eventRepository = $eventRepository;
        $this->participantExportService = $participantExportService;
    }

    /**
     * @param $slug
     * @return string
     */
    public function exportParticipantsToCsv($slug)
    {
        $event   = $this->eventRepository->findBySlug($slug);
        $content = $this->participantExportService->exportEventParticipantsToCsv($event);

        $callback = function() use ($content) {
            $file = fopen('php://output', 'w');
            fputs($file, $content);
            fclose($file);
        };

        $fileName = "participants-export-".$slug."-".Carbon::now()->toISOString().".csv";
        return Response::stream($callback, 200, Csv::getResponseHeader($fileName));
    }
}
