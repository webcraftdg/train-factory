<?php

namespace App\Datapipeline\Service;

use App\Datapipeline\Format\SessionPipelineDataFormat;
use App\Datapipeline\Io\Output\SessionXlsxOutput;
use App\Datapipeline\Processor\SessionProcessor;
use App\Entity\Session;
use App\Repository\SessionRepository;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\configs\ProcessorConfig;
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


class ExportSessionService
{

    private string $exportFilePath;

    public function __construct(
        private string $name,
        private int $version,
        private array $rows,
        private bool $stopOnError,
        private SessionRepository $sessionRepository,
        private array $options = []
        )
    {
        $this->exportFilePath = ($this->options['exportFilePath']) ?? '';
    }

    public function createPipeline() : PipelineConfig
    {
        $columnsMappingService = new ColumnsMappingService();
        $columnsMappingService->addColumn('id', 'id');
        $source = new SourceConfig(
            type: DataEndpointType::ARRAY,
            name: PipelineDataFormat::ARRAY,
            options : [
                'rows' => $this->rows
            ]
        );
        $target =  new TargetConfig(DataEndpointType::FILE, SessionPipelineDataFormat::SESSION_XLSX, [
            'path' => $this->exportFilePath
        ]);
        return new PipelineConfig(
            name: $this->name,
            version: $this->version,
            stopOnError: $this->stopOnError,
            source: $source,
            target: $target,
            columns : $columnsMappingService->getColumns(),
            processor : new ProcessorConfig('session-load', ['repository' => $this->sessionRepository]),
            options: $this->options
        );

    }

    public function run() : ExecutionReport
    {
        $config = $this->createPipeline();

        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(),
            new OutputRegistry([SessionPipelineDataFormat::SESSION_XLSX => SessionXlsxOutput::class]),
            new ProcessorRegistry(['session-load' => SessionProcessor::class]),
            new TransformerRegistry()
        ))->create($config);


        $executor = new PipelineExecutor();
        return $executor->run($config, $pipelinRuntime);
    }
}
