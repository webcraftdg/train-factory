<?php
/**
 * TableOutput.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package app\io\outputs
 */
namespace app\io\outputs;

use App\Datapipeline\Io\Writer\ExcelWriter;
use app\io\writers\DbWriter;
use webcraftdg\dataPipeline\interfaces\OutputInterface;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\interfaces\RuntimeContextInterface;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;
use webcraftdg\dataPipeline\rules\FileRules;

class TableOutput implements OutputInterface, ValidateRulesInterface
{
    /**
     * $writer
     *
     * @var DbWriter
     */
    private  ExcelWriter $writer;
    private array $options;
    private string $transactionMode = 'none';
    private int $batchSize = 250;


    /**
     * constructor
     *
     * @param  PipelineConfig               $config
     * @param  RuntimeContextInterface|null $context
     */
    public function __construct(
        private PipelineConfig $config,
        ?RuntimeContextInterface $context = null)
    {
        $this->options = $this->config->target->getOptions();
        $this->batchSize = ($this->options['batchSize']) ?? $this->batchSize;
        $this->transactionMode = ($this->options['transactionMode']) ?? $this->transactionMode;
        $this->writer = new ExcelWriter();
    }

     /**
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        return array_merge([], FileRules::rulesBatchSize());
    }

    /**
     * open
     *
     * @return void
     */
    public function open(): void
    {
        $this->writer->open();
    }


    /**
     * write
     *
     * @param  array              $row
     *
     * @return void
     */
    public function write(array $row): void
    {
        $this->writer->write($row);
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        $this->writer->close();
    }
}
