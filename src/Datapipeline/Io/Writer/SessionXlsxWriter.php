<?php
/**
 * SessionXlsxWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\writers
 */
namespace App\Datapipeline\Io\Writer;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\interfaces\DataWriterInterface;
use webcraftdg\dataPipeline\exceptions\WriterException;


class SessionXlsxWriter implements DataWriterInterface
{
    const ROW_HEIGHT = 15;
    const TO_MERGED = 'toMerged';
    private Spreadsheet $spreadsheet;
    private ?string $path;
    private int $written = 0;

    private static $defaultFont = [
        'bold' => false,
        'size' => 9,
    ];
    private static array $defaultBorders = [
        'outline' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => '00000000'], // noir
        ],
    ];

    private static array $columnTitles = [
        'A' => '',
        'B' => 'Tireur A',
        'C' => 'Tireur B',
        'D' => 'Consigne',
        'E' => 'Objectifs',
        'F' => 'Durée'
    ];

    public function __construct(
        private PipelineConfig $config,
        private array $options = [],
    ) {
        $this->spreadsheet = new Spreadsheet();
        $this->path = $options['path'] ?? null;
    }

    public function open(): void
    {
        if (!is_string($this->path) || $this->path === '') {
            throw new WriterException('SessionXlsxWriter : option "path" manquante.');
        }
    }

    public function write(array $row): void
    {
        if (!isset($row['name']) || !is_array($row['blocks'] ?? null)) {
            throw new WriterException('Séance invalide : "name" et "blocks" sont requis.');
        }

        $sheet = $this->written === 0
            ? $this->spreadsheet->getActiveSheet()
            : $this->spreadsheet->createSheet();

        $sheet->setTitle($this->uniqueSheetTitle((string) $row['name']));
        $this->writeSheet($sheet, $row);
        $this->written++;
    }

    private function writeSheet(Worksheet $sheet, array $session): void
    {
        $this->configureSheet($sheet);
        $line = 1;
        $this->configureLineStyle($sheet, $line, 'A');
        $sheet->setCellValue("A{$line}", 'THEME');
        $this->configureLineStyle($sheet, $line, 'B', 'E');
        $this->mergedLine($sheet, "B{$line}:E{$line}", $session['target'].' : '.$session['description'], 'title');
        $this->configureLineStyle($sheet, $line, 'F');
        $sheet->setCellValue("F{$line}", (string) 'Durée totale : '.($session['duration'] ?? '') . ' min');
        $sheet->getRowDimension($line++)->setRowHeight(self::ROW_HEIGHT);
        $this->writeLine($sheet, $line, 'SALUT', 'A', 'F');
        $sheet->getRowDimension($line++)->setRowHeight(self::ROW_HEIGHT);


        foreach (self::$columnTitles as $col => $title) {
            $sheet->setCellValue("{$col}{$line}", $title);
            $this->configureLineStyle($sheet, $line, $col);
        }
        $sheet->getRowDimension($line++)->setRowHeight(self::ROW_HEIGHT);

        // L'ordre fourni par SessionService::getOrderedBlocks() est conservé.
        foreach ($session['blocks'] as $block) {
            if (!is_array($block)) {
                continue;
            }
            $this->writeBlock($sheet, $line, $block);
        }
        $this->writeLine($sheet, $line, 'SALUT', 'A', 'F');
        /*
        $last = max(4, $line - 1);
        $sheet->getPageSetup()->setPrintArea("A1:F{$last}");
        $sheet->getStyle("A4:F{$last}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFD5DCE4');
            */
    }

    private function writeLine(Worksheet $sheet, int $line, string $text, string $colStart, ?string $colEnd = null)
    {
        $colEnd = ($colEnd) ?? $colStart;
        $this->configureLineStyle($sheet, $line, $colStart, $colEnd);
        $this->mergedLine($sheet, "{$colStart}{$line}:{$colEnd}{$line}", $text, 'title');
    }

    private function writeBlock(Worksheet $sheet, int &$line, array $block) :void
    {
        $this->configureLineStyle($sheet, $line, 'A', 'F');
        $sheet->setCellValue("A{$line}", (string) ($block['name'] ?? ''));
        $sheet->setCellValue("F{$line}", (string) ($block['duration'] ?? '') . ' min');
        $sheet->getRowDimension($line++)->setRowHeight(self::ROW_HEIGHT);

        $globalDescription = trim((string) ($block['description'] ?? ''));
        $globalObjective = trim((string) ($block['objective'] ?? ''));
        $globalInstruction = trim((string) ($block['instruction'] ?? ''));

        $hasDescription = false;
        $hasInstruction = false;
        $hasObjective = false;
        $descLines = [$line];
        $instrLines = [$line];
        $objLines = [$line];
        foreach (($block['activities'] ?? []) as $activity) {
            if (!is_array($activity)) {
                continue;
            }
            $hydration = ($activity['hydration']) ?? false;
            $this->writeActivity($sheet, $line, $activity, $hasDescription, $hasInstruction, $hasObjective);
            $description = ($activity['description']) ?? $globalDescription;
            $instruction = ($activity['instruction']) ?? $globalObjective;
            $objective = ($activity['objective']) ?? $globalInstruction;
            if($hasDescription === false) {
                $descLines[] = $line;
            }
            /*
            if ($hasDescription === true) {
                if (count($descLines) > 1) {
                    $this->mergeVertical($sheet, $descLines, $globalDescription);
                    $hasDescription = false;
                }
                $descLines = [];
            } else {
                $descLines[] = $line;
            }
            if ($hasInstruction ===true) {
                if (count($instrLines) > 1) {
                    $this->mergeVertical($sheet, $instrLines, $instruction, 'D');
                    $hasInstruction = false;
                }
                $instrLines = [];
            } else {
                $instrLines[] = $line;
            }
            if ($hasObjective === true && count($objLines) > 1) {
                if (count($objLines) > 1) {
                    $this->mergeVertical($sheet, $objLines, $objective, 'E');
                    $hasObjective = false;
                }
                $objLines = [];
            } else {
                $objLines[] = $line;
            }
                */

            if ($hydration === true) {
                $this->writeLine($sheet, $line, 'Hydratation', 'A', 'E');
                $this->writeLine($sheet, $line, '2 min', 'F', 'F');
                $line ++;
            }
        }

        if (count($descLines) > 1) {
            $this->mergeVertical($sheet, $descLines, $globalDescription);
            $hasDescription = false;
        }
        /*
        if ($hasDescription ===false && empty($descLines) === false) {
            $this->mergeVertical($sheet, $descLines, $globalDescription);
            $descLines = [];
            $hasDescription = false;
        }
        if ($hasInstruction ===false && empty($instrLines) === false) {
            $this->mergeVertical($sheet, $instrLines, $globalInstruction, 'D');
            $instrLines = [];
            $hasInstruction = false;
        }
        if ($hasObjective ===false && empty($objLines) === false) {
            $this->mergeVertical($sheet, $objLines, $globalObjective, 'E');
            $objLines = [];
            $hasObjective = false;
        }*/
    }


    private function mergeVertical(Worksheet $sheet, array $lines, string $value, $columnName = 'A')
    {
        $posA = array_shift($lines);
        $posB = (empty($lines) === false) ? array_pop($lines) : $posA;
        $range = "{$columnName}{$posA}:{$columnName}{$posB}";
        $sheet->mergeCells($range);
        $sheet->setCellValue("{$columnName}{$posA}", $value);
        $this->configureLineStyle($sheet, $posA, $columnName);
    }
    private function writeActivity(
        Worksheet $sheet,
        int &$line,
        array $activity,
        bool &$hasDescription = false,
        bool &$hasInstruction = false,
        bool &$hasObjective = false
    ): void
    {
        $exercises = array_values(array_filter(
            $activity['exercises'] ?? [],
            static fn (mixed $item): bool => is_array($item)
        ));

        // Convention TEMPORAIRE, à remplacer par une donnée métier :
        // premier exercice = Tireur A, deuxième = Tireur B.
        // Les exercices supplémentaires sont affichés dans la colonne A.
        $hasDescription = (empty($activity['description']) === false);
        $hasInstruction = (empty($activity['instruction']) === false);
        $hasObjective = (empty($activity['objective']) === false);
        $values = [];
        $value = [];
        foreach($exercises as $index => $exercise) {
            $textsExercise = [
                $exercise['name'],
                ($exercise['description']) ?? null
            ];
            $value[] = implode(' : ', $textsExercise);
            if ($index > 0 && $index % 2 == 0) {
                if (count($value) == 1) {
                    $value[] = self::TO_MERGED;
                }
                $values[] = $value;
                $value = [];
            }
        }
        if (empty($values) === true) {
            if (count($value) == 1) {
                $value[] = self::TO_MERGED;
            }
            $values[] = $value;
        }

        $finaleValues = [];
        foreach($values as $item) {
            array_unshift($item, (($activity['description']) ?? null));
            array_push($item, (($activity['instruction']) ?? null), (($activity['objective']) ?? null), (string) ($activity['duration'] ?? ''));
            $item = array_combine(['A', 'B', 'C', 'D', 'E', 'F'], $item);
            $finaleValues[] = $item;
        }

        foreach ($finaleValues as $indexValue => $row) {
            $prevColumn = null;
            foreach($row as $col => $value) {
                $sheet->setCellValue($col.$line, $value);
                if ($value === self::TO_MERGED && $prevColumn !== null) {
                    $range = "{$prevColumn}{$line}:{$col}{$line}";
                    $sheet->mergeCells($range);
                    $this->configureLineStyle(
                        sheet:$sheet,
                        line:$line,
                        colStart:$prevColumn,
                        colEnd:$col,
                        alignHorizontal:Alignment::HORIZONTAL_LEFT);
                } else {
                    $align = ($col == 'F') ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT;
                    $this->configureLineStyle(
                        sheet:$sheet,
                        line:$line,
                        colStart:$col,
                        alignHorizontal:$align);
                }

                $prevColumn = $col;
            }
            $line ++;
        }
        /*
        $this->styleRange($sheet, "A{$line}:F{$line}", 'activity');
        $sheet->getStyle("A{$line}")->getFont()->setBold(true);
        $sheet->getStyle("F{$line}")->getFont()->setBold(true);
        $sheet->getStyle("F{$line}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $maxLines = 1;
        $charsPerLine = [23, 30, 30, 34, 27, 12];
        foreach ($values as $i => $value) {
            $maxLines = max($maxLines, $this->estimatedLines($value, $charsPerLine[$i]));
        }
        $sheet->getRowDimension($line++)->setRowHeight(min(125, max(30, 13 * $maxLines + 7)));
        */
    }

    private function configureLineStyle(
        Worksheet $sheet,
        string $line,
        string $colStart,
        ?string $colEnd = null,
        array $borders = [],
        array $font = [],
        string $alignHorizontal = Alignment::HORIZONTAL_CENTER,
        string $alignVertical = Alignment::HORIZONTAL_CENTER,
        )
    {
        $arrayStyle = [];
        $arrayStyle['font'] = ($font) ?: self::$defaultFont;
        $arrayStyle['borders'] = ($borders) ?: self::$defaultBorders;
        $cellCoordinate = $colStart.$line.(($colEnd) ? ':'.$colEnd.$line : null);
        $sheet->getStyle($cellCoordinate)->applyFromArray($arrayStyle)
        ->getAlignment()
        ->setHorizontal($alignHorizontal)
        ->setVertical($alignVertical);
    }
    private function configureSheet(Worksheet $sheet): void
    {
        foreach (['A' => 25, 'B' => 25, 'C' => 25, 'D' => 25, 'E' => 25, 'F' => 25] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getStyle('A:F')->getFont()->setSize(9);
        $sheet->getStyle('A:F')->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $page = $sheet->getPageSetup();
        $page->setPaperSize(PageSetup::PAPERSIZE_A4);
        $page->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $page->setFitToPage(true);
        $page->setFitToWidth(1);
        $page->setFitToHeight(1);

        $sheet->getPageMargins()->setTop(0.25)->setBottom(0.25)
            ->setLeft(0.25)->setRight(0.25)
            ->setHeader(0.1)->setFooter(0.1);
        $sheet->getHeaderFooter()->setOddFooter('TrainFactory  •  Page &P / &N');
        $sheet->setShowGridLines(false);
    }

    private function mergedLine(Worksheet $sheet, string $range, string $text, string $kind): void
    {
        $sheet->mergeCells($range);
        $first = explode(':', $range)[0];
        $sheet->setCellValue($first, $text);
        $this->styleRange($sheet, $range, $kind);
    }

    private function styleRange(Worksheet $sheet, string $range, string $kind): void
    {
        $style = match ($kind) {
            'title' => ['fill' => 'FFFFFFFF', 'font' => '00000000', 'bold' => false, 'size' => 9],
            'subtitle' => ['fill' => 'FFFFFFFF', 'font' => '00000000', 'bold' => false, 'size' => 9],
            'header' => ['fill' => 'FFFFFFFF', 'font' => '00000000', 'bold' => false, 'size' => 9],
            'block' => ['fill' => 'FFFFFFFF', 'font' => '00000000', 'bold' => false, 'size' => 9],
            'meta' => ['fill' => 'FFFFFFFF', 'font' => '00000000', 'bold' => false, 'size' => 9],
            'context' => ['fill' => 'FFFFFFFF', 'font' => '00000000', 'bold' => false, 'size' => 9],
            default => ['fill' => 'FFFFFFFF', 'font' => '00000000', 'bold' => false, 'size' => 9],
        };

        $s = $sheet->getStyle($range);
        $s->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($style['fill']);
        $s->getFont()->setBold($style['bold'])->setSize($style['size'])
            ->getColor()->setARGB($style['font']);
        $s->getAlignment()->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function estimatedLines(string $value, int $charsPerLine): int
    {
        $lines = 0;
        foreach (explode("\n", $value) as $segment) {
            $lines += max(1, (int) ceil(mb_strlen($segment) / $charsPerLine));
        }
        return $lines;
    }

    private function height(string $value, int $charsPerLine, int $minimum, int $maximum): int
    {
        return min($maximum, max($minimum, 13 * $this->estimatedLines($value, $charsPerLine) + 6));
    }

    private function uniqueSheetTitle(string $title): string
    {
        $base = trim((string) preg_replace('~[\\\\/*?:\[\]]~u', '-', $title));
        $base = mb_substr($base !== '' ? $base : 'Séance', 0, 31);
        $candidate = $base;
        $suffix = 2;
        while ($this->spreadsheet->getSheetByName($candidate) !== null) {
            $end = '-' . $suffix++;
            $candidate = mb_substr($base, 0, 31 - strlen($end)) . $end;
        }
        return $candidate;
    }

    public function close(): void
    {
        if ($this->written === 0) {
            throw new WriterException('Aucune séance à exporter.');
        }
        if ($this->path === null) {
            throw new WriterException('Chemin d’export absent.');
        }

        (new Xlsx($this->spreadsheet))->save($this->path);
        $this->spreadsheet->disconnectWorksheets();
    }
}
