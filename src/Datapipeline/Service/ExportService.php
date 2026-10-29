<?php

namespace App\Datapipeline\Service;

use App\Entity\Session;
use app\Repository\SessionRepository;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\configs\SourceConfig;
use webcraftdg\dataPipeline\configs\TargetConfig;
use webcraftdg\dataPipeline\supports\enums\DataEndpointType;
use webcraftdg\dataPipeline\supports\enums\PipelineDataFormat;
use webcraftdg\dataPipeline\runtimes\PipelineRuntimeFactory;
use webcraftdg\dataPipeline\registry\InputRegistry;
use webcraftdg\dataPipeline\registry\OutputRegistry;
use webcraftdg\dataPipeline\registry\ProcessorRegistry;
use webcraftdg\dataPipeline\registry\TransformerRegistry;
use webcraftdg\dataPipeline\pipelines\PipelineExecutor;
use webcraftdg\dataPipeline\pipelines\ExecutionReport;


class ExportService
{

    private SessionService $sessionService;
    private string $exportFilePatch;

    public function __construct(
        private string $name,
        private int $version,
        private bool $stopOnError,
        private Session $session,
        private SessionRepository $sessionRepository,
        private array $options = []
        )
    {
        $this->sessionService = new SessionService(
            session:$session,
            sessionRepository: $sessionRepository
        );
        $this->exportFilePatch = ($this->options['exportFilePatch']) ?? null;
    }

    public function createPipeline() : PipelineConfig
    {
        $rows = $this->sessionService->getRows();
        $source = new SourceConfig(
            type: DataEndpointType::ARRAY,
            name: PipelineDataFormat::ARRAY,
            options : [
                'rows' => $rows
            ]
        );
        $target =  new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::EXCEL_X, [
            'path' => $this->exportFilePatch
        ]);
        $pipeline = new PipelineConfig(
            name: $this->name,
            version: $this->version,
            stopOnError: $this->stopOnError,
            source: $source,
            target: $target,
            columns : [],
            processor : null,
            options: $this->options
        );
        return $pipeline;

    }

    public function run() : ExecutionReport
    {
        $config = $this->createPipeline();
        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(),
            new OutputRegistry(),
            new ProcessorRegistry(),
            new TransformerRegistry()
        ))->create($config);


        $executor = new PipelineExecutor();
        return $executor->run($config, $pipelinRuntime);
    }
}
