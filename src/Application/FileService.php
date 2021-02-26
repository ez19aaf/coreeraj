<?php

namespace Survey54\Reap\Application;

use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Shape\Drawing\Base64;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SamChristy\PieChart\PieChartGD;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\QuestionType;
use Survey54\Reap\Application\Helper\PdfHelper;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Domain\Survey;
use Survey54\Reap\Framework\Exception\Error;

class FileService
{
    private SurveyRepository $surveyRepository;
    private ResponseService $responseService;

    /**
     * FileService constructor.
     * @param SurveyRepository $surveyRepository
     * @param ResponseService $responseService
     */
    public function __construct(
        SurveyRepository $surveyRepository,
        ResponseService $responseService
    ) {
        $this->surveyRepository = $surveyRepository;
        $this->responseService  = $responseService;
    }

    /**
     * @param string $surveyId
     * @return array|mixed
     */
    public function createSpreadSheet(string $surveyId)
    {
        /** @var $survey Survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_SURVEY_NOT_FOUND);
        }

        $surveyTitle     = str_replace([':', ' '], ['', '_'], $survey->title);
        $surveyQuestions = $survey->questions;
        $questionCount   = count($surveyQuestions);

        $excel = new Spreadsheet();

        try {
            $dataSheet = $excel->setActiveSheetIndex(0)->setTitle('Data');
            $dataSheet->getStyle('1:1')->getFont()->setBold(true)->setSize(14);

            $dataSheet->setCellValue('A1', 'S/N');
            $dataSheet->setCellValue('B1', 'Question');
            $dataSheet->setCellValue('C1', 'Type');
            $dataSheet->setCellValue('D1', 'Options');

            $columns = ['A', 'B', 'C', 'D'];
            $row     = 0;
            foreach ($surveyQuestions as $index => $question) {
                $qid           = $question['id'];
                $questionType  = $question['type'];
                $questionSheet = $excel->createSheet($qid)->setTitle('Q' . $qid);
                $questionSheet->getStyle('1:1')->getFont()->setBold(true)->setSize(14);

                $data = [
                    'offset'     => 0,
                    'limit'      => 10000,
                    'surveyId'   => $surveyId,
                    'questionId' => $qid,
                    'orRepeat'   => 1,
                ];

                $responseList = $this->responseService->list($data);

                $question['answers']  = [];
                $question['metadata'] = [];
                foreach ($responseList as $response) {
                    $reason = false;
                    if ($questionType === QuestionType::SINGLE_CHOICE || $questionType === QuestionType::MULTIPLE_CHOICE || $questionType === QuestionType::RANKING) {
                        $choices = [];
                        foreach ($response['answerIds'] as $answerId) {
                            $choices[] = $question['options'][(int)$answerId - 1]['text'];
                            if ((isset($question['withOther']) && $question['withOther'] === true) || (isset($question['withReason']) && $question['withReason'] === true)) {
                                $reason = $response['answer'];
                            }
                        }
                        $answer = implode(',', $choices);
                    } else if ($questionType === QuestionType::SCALE) {
                        $answer = $response['answerScale'];
                    } else {
                        $answer = $response['answer'];
                    }
                    if ($reason) {
                        $question['reason'][count($question['answers'])] = $reason;
                    }
                    $question['answers'][]  = $answer;
                    $question['metadata'][] = [
                        'ageGroup'   => $response['ageGroup'],
                        'employment' => $response['employment'],
                        'gender'     => $response['gender'],
                        'lsmGroup'   => $response['lsmGroup'],
                        'race'       => $response['race'],
                    ];
                }

                $this->excelChart($questionSheet, $question, $questionType);

                foreach ($columns as $column) {
                    $reason = false;
                    switch ($column) {
                        case 'A':
                            $value = $question['id'];
                            $row   += 2;
                            break;
                        case 'B':
                            $value = $question['text'];
                            break;
                        case 'C':
                            $value = $question['type'];
                            break;
                        case 'D':
                            if ($questionType === QuestionType::SINGLE_CHOICE || $questionType === QuestionType::MULTIPLE_CHOICE || $questionType === QuestionType::RANKING) {
                                $options         = $question['options'];
                                $questionOptions = '';
                                foreach ($options as $option) {
                                    $questionOptions .= $option['id'] . '. ' . $option['text'] . ',  ';
                                    if ((isset($question['withOther']) && $question['withOther'] === true)) {
                                        $reason = "Other:";
                                    }
                                    if (isset($question['withReason']) && $question['withReason'] === true) {
                                        $reason = "Reason:";
                                    }
                                }
                                $value = $questionOptions;
                            } else if ($questionType === QuestionType::SCALE) {
                                $range         = $question['scale'];
                                $questionRange = 'From ' . $range['from'] . ' to ' . $range['to'] . '.';
                                $value         = $questionRange;
                            } else {
                                $value = '-';
                            }
                            break;
                    }
                    $dataSheet->setCellValue($column . $row, $value);
                    $rowAnswer = $row;
                    if ($reason) {
                        $row++;
                        $dataSheet->setCellValue($column . $row, $reason);
                    }
                }

                $col = 5;
                foreach ($question['answers'] as $key => $ans) {
                    $dataSheet->setCellValueByColumnAndRow($col, 1, 'R' . ($key + 1));
                    $dataSheet->setCellValueByColumnAndRow($col, $rowAnswer, $ans);
                    if (isset($question['reason'][$key])) {
                        $dataSheet->setCellValueByColumnAndRow($col, $rowAnswer + 1, $question['reason'][$key]);
                    }
                    $col++;
                }

                $metadataRow = ($questionCount * 3);
                $dataSheet->setCellValue('D' . ($metadataRow + 3), 'Demographics')->getStyle('D' . ($metadataRow + 3))->getFont()->setBold(true)->setSize(14);
                $dataSheet->setCellValue('D' . ($metadataRow + 4), 'Age Group');
                $dataSheet->setCellValue('D' . ($metadataRow + 5), 'Gender');
                $dataSheet->setCellValue('D' . ($metadataRow + 6), 'Employment');

                if ($survey->countries[0] === Country::SOUTH_AFRICA) {
                    $dataSheet->setCellValue('D' . ($metadataRow + 7), 'Race');
                    $dataSheet->setCellValue('D' . ($metadataRow + 8), 'LSM Group');
                }

                $dCol = 5;
                foreach ($question['metadata'] as $demographics) {
                    $dataSheet->setCellValueByColumnAndRow($dCol, ($metadataRow + 4), array_key_exists('ageGroup', $demographics) ? $demographics['ageGroup'] : '');
                    $dataSheet->setCellValueByColumnAndRow($dCol, ($metadataRow + 5), array_key_exists('gender', $demographics) ? $demographics['gender'] : '');
                    $dataSheet->setCellValueByColumnAndRow($dCol, ($metadataRow + 6), array_key_exists('employment', $demographics) ? $demographics['employment'] : '');

                    if ($survey->countries[0] === Country::SOUTH_AFRICA) {
                        $dataSheet->setCellValueByColumnAndRow($dCol, ($metadataRow + 7), array_key_exists('race', $demographics) ? $demographics['race'] : '');
                        $dataSheet->setCellValueByColumnAndRow($dCol, ($metadataRow + 8), array_key_exists('lsmGroup', $demographics) ? $demographics['lsmGroup'] : '');
                    }

                    $dCol++;
                }
            }

            foreach ($dataSheet->getColumnIterator() as $col) {
                $dataSheet->getColumnDimension($col->getColumnIndex())->setAutoSize(true);
            }
            $dataSheet->getParent()->getDefaultStyle()->applyFromArray([
                'font' => [
                    'size' => 14,
                ],
            ]);
            $excel->setActiveSheetIndex(0);

            $excelWriter = new Xlsx($excel);

            $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
            $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
            $excelWriter->setIncludeCharts(true);
            $excelWriter->save($tempFile);
        } catch (Exception $e) {
            Error::throwError(Error::S54_INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        $response = [
            'file'        => $tempFile ?? null,
            'surveyTitle' => $surveyTitle,
        ];

        return $response;
    }

    /**
     * @param string $surveyId
     * @return array|mixed
     */
    public function createPdf(string $surveyId)
    {
        /** @var Survey $survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_SURVEY_NOT_FOUND);
        }

        $survey          = $survey->toArray();
        $currentYear     = date('Y');
        $surveyTitle     = str_replace(":", "", $survey['title']);
        $surveyTitleFile = str_replace([':', ' '], ['', '_'], $survey['title']);
        $surveyQuestions = $survey['questions'];

        $pdf = new PdfHelper();
        $pdf->AddPage();

        if ($survey['image']) {
            $pdf->Image($survey['image'], 30, 45, 155, 100);
        }
        $pdf->SetFont('Arial', '', 33);
        $pdf->setX(50);
        $pdf->setY(165);
        $pdf->MultiCell(185, 12, $surveyTitle, 0, 'C', false);

        foreach ($surveyQuestions as $index => $question) {
            $questionType = $question['type'];
            $data         = [
                'offset'     => 0,
                'limit'      => 10000,
                'surveyId'   => $surveyId,
                'questionId' => $question['id'],
                'orRepeat'   => 1,
            ];

            $surveyResponse = $this->responseService->list($data);

            $question['answers'] = [];

            foreach ($surveyResponse as $key => $response) {
                if ($questionType === QuestionType::SINGLE_CHOICE || $questionType === QuestionType::MULTIPLE_CHOICE) {
                    $choices = [];
                    foreach ($response['answerIds'] as $answerId) {
                        $choices[] = $question['options'][(int)$answerId - 1]['text'];
                    }
                    $answer = implode(',', $choices);
                } else if ($questionType === QuestionType::RANKING) {
                    $answer = $response['answerRank'];
                } else if ($questionType === QuestionType::SCALE) {
                    $answer = $response['answerScale'];
                } else {
                    $answer = $response['answer'];
                }
                $question['answers'][] = $answer;
            }

            $tableData = $this->createTableData($question, $question['type']);

            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);
            // Set Left Margin
            $pdf->SetLeftMargin(7);
            // Question
            $questionText = wordwrap($question['text'], 71, "<br />");
            $questionText = explode("<br />", $questionText);
            $y            = 13;
            $pdf->text(5, $y, 'Q' . $question['id'] . ' - ' . $questionText[0]);

            foreach ($questionText as $key => $text) {
                if ($key === 0) {
                    continue;
                }
                $y += 6;
                $pdf->text(15, $y, $text);
            }

            // Logo
            if ($survey['image']) {
                $pdf->Image($survey['image'], 165, 6, 40, 12);
            }
            // Line break
            $pdf->Ln(20);

            $chartData = [];
            foreach ($tableData['data'] as $value) {
                $chartData[$value[0]] = $value[2];
            }

            if ($question['type'] !== QuestionType::OPEN_ENDED) {
                $y += 95;
                $pdf->createChartPdf($chartData, $question['type'], $y);
                $y += 45;
            } else {
                $y = $y < 25 ? 25 : $y + 25;
            }
            $pdf->questionTable($tableData['headers'], $tableData['data'], $y);
            if ($question['type'] === QuestionType::OPEN_ENDED) {
                $pdf->SetTextColor(255, 0, 0);
                $pdf->Cell(170, 6, '(for more of the responses please use Excel download or web dashboard)', 1, 0, 'L', true);
                $pdf->Ln();
                $pdf->SetTextColor(0);
            }
        }

        $pdf->AddPage();

        $pdf->SetY(50);
        $pdf->Cell(80);
        $pdf->Image(__DIR__ . '/assets/images/logo.png', 30, 45, 150, 50, "", "https://www.survey54.com");
        $pdf->SetY(110);
        $pdf->SetX(0);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(200, 10, 'Survey54 | ' . iconv("UTF-8", "ISO-8859-1", "©") . ' ' . $currentYear, 0, 0, 'C', false, "https://www.survey54.com");
        $pdf->Output();
        $pdf->Close();

        $response = [
            'file'        => $pdf ?? null,
            'surveyTitle' => $surveyTitleFile,
        ];

        return $response;
    }

    /**
     * @param string $surveyId
     * @return array
     * @throws \Exception
     */
    public function createPpt(string $surveyId): array
    {
        /** @var Survey $survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_SURVEY_NOT_FOUND);
        }

        $survey          = $survey->toArray();
        $currentYear     = date('Y');
        $surveyTitle     = str_replace(":", "", $survey['title']);
        $surveyTitleFile = str_replace([':', ' '], ['', '_'], $survey['title']);
        $surveyQuestions = $survey['questions'];

        $objPHPPowerPoint = new PhpPresentation();

        // Get Active Slide
        $firstSlide = $objPHPPowerPoint->getActiveSlide();

        // Create a shape (drawing)
        $survey54Logo = $firstSlide->createDrawingShape();
        $survey54Logo->setName('Survey54 logo')
            ->setDescription('Survey54 logo')
            ->setPath(__DIR__ . '/assets/images/logo-height-25px.png')
            ->setHeight(40)
            ->setOffsetX(30)
            ->setOffsetY(30);

        if ($survey['image']) {
            // Add a file drawing (JPEG) to the slide
            $surveyImage = new Base64();
            $imageData   = "data:image/jpeg;base64," . base64_encode(file_get_contents($survey['image']));

            $surveyImage->setName('Survey logo')
                ->setDescription('Survey logo')
                ->setData($imageData)
                ->setResizeProportional(false)
                ->setHeight(400)
                ->setWidth(800)
                ->setOffsetX(80)
                ->setOffsetY(120);
            $firstSlide->addShape($surveyImage);
        }

        // Create a shape (text)
        $shape = $firstSlide->createRichTextShape()
            ->setHeight(50)
            ->setWidth(800)
            ->setOffsetX(80)
            ->setOffsetY(530);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $shape->createTextRun($surveyTitle)->getFont()->setBold(true)->setSize(30);

        foreach ($surveyQuestions as $index => $question) {
            $questionType = $question['type'];
            $data         = [
                'offset'     => 0,
                'limit'      => 10000,
                'surveyId'   => $surveyId,
                'questionId' => $question['id'],
                'orRepeat'   => 1,
            ];

            $surveyResponse = $this->responseService->list($data);

            $question['answers'] = [];

            foreach ($surveyResponse as $key => $response) {
                if ($questionType === QuestionType::SINGLE_CHOICE || $questionType === QuestionType::MULTIPLE_CHOICE) {
                    $choices = [];
                    foreach ($response['answerIds'] as $answerId) {
                        $choices[] = $question['options'][(int)$answerId - 1]['text'];
                    }
                    $answer = implode(',', $choices);
                } else if ($questionType === QuestionType::RANKING) {
                    $answer = $response['answerRank'];
                } else if ($questionType === QuestionType::SCALE) {
                    $answer = $response['answerScale'];
                } else {
                    $answer = $response['answer'];
                }
                $question['answers'][] = $answer;
            }

            // Question slide
            $tableData = $this->createTableData($question, $question['type']);

            $questionSlide = $objPHPPowerPoint->createSlide();

            $this->pptQuestionSlideHeader($survey, $question, $questionSlide);

            $this->pptQuestionTable($questionSlide, $tableData, $question['type']);

            $this->pptFooter($questionSlide, $currentYear);

            $this->pptQuestionChart($questionSlide, $question['type'], $tableData['data']);
        }

        // Last Slide
        $lastSlide = $objPHPPowerPoint->createSlide();

        // Create a shape (drawing)
        $survey54Logo = $lastSlide->createDrawingShape();
        $survey54Logo->setName('Survey54 logo')
            ->setDescription('Survey54 logo')
            ->setPath(__DIR__ . '/assets/images/logo.png')
            ->setHeight(100)
            ->setWidth(600)
            ->setOffsetX(180)
            ->setOffsetY(200);

        // Create a shape (text)
        $lastSlideYear = $lastSlide->createRichTextShape()
            ->setHeight(50)
            ->setWidth(400)
            ->setOffsetX(80)
            ->setOffsetY(330);
        $lastSlideYear->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $lastSlideYear->createTextRun('©' . $currentYear)->getFont()->setSize(20);

        // Create a shape (text)
        $lastSlideSurveyUrl = $lastSlide->createRichTextShape()
            ->setHeight(50)
            ->setWidth(550)
            ->setOffsetX(270)
            ->setOffsetY(330);
        $lastSlideSurveyUrl->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $lastSlideSurveyUrl->createTextRun('https//www.survey54.com')->getFont()->setBold(true)->setSize(20);

        $this->pptFooter($lastSlide, $currentYear);

        $oWriterPPTX = IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
        $tempFile    = tempnam(File::sysGetTempDir(), 'phpxltmp');
        $tempFile    = $tempFile ?: __DIR__ . '/temp.pptx';
        $oWriterPPTX->save($tempFile);

        $response = [
            'file'        => $tempFile ?? null,
            'surveyTitle' => $surveyTitleFile,
        ];

        return $response;
    }

    /**
     * Used in excel
     * @param Worksheet $worksheet
     * @param $question
     * @param mixed $questionType
     */
    private function excelChart(Worksheet $worksheet, $question, $questionType): void
    {
        $answers = $question['answers'];
        $name    = $worksheet->getTitle();


        $total = count($answers);


        $counts = array_count_values($answers);

        $chartColumns = count($counts);

        $data = [
            [$question['text'], ($questionType === QuestionType::OPEN_ENDED) ? 'Frequency' : 'Count', 'Percentage %'],
        ];

        if ($questionType === QuestionType::SINGLE_CHOICE || $questionType === QuestionType::MULTIPLE_CHOICE || $questionType === QuestionType::RANKING) {
            if ($question['answers']) {
                foreach ($counts as $key => $value) {
                    $data[] = [$key, $value, ceil(($value / $total) * 100)];
                }
            }
        }

        if ($questionType === QuestionType::SCALE) {
            $range = $question['scale'];
            if ($question['answers']) {
                for ($scale = $range['from']; $scale <= $range['to']; $scale++) {
                    if (array_key_exists($scale, $counts)) {
                        $data[] = [$scale, $counts[$scale], ceil(($counts[$scale] / $total) * 100)];
                    } else {
                        $data[] = [$scale, 0, 0];
                    }
                }
            }
            $chartColumns = $range['to'];
        }
        $keywords        = [];
        $keywordResponse = [];
        if (($questionType === QuestionType::OPEN_ENDED) && $question['answers']) {
            $analyses        = $this->getOpenEndedAnalysis($question);
            $keywordResponse = $analyses;

            foreach ($analyses as $key => $analysis) {
                $keywords[$key] = $analysis[0];
            }
            $chartColumns = count($keywords);
            foreach ($keywords as $key => $value) {
                $data[] = [$key, $value, ceil(($value / $total) * 100)];
            }
        }

        ++$chartColumns;
        $worksheet->fromArray($data);
        $totalValueRow = $chartColumns + 2;
        $totalCell     = $worksheet->setCellValueByColumnAndRow(1, $chartColumns + 2, 'Total');
        $totalCell->getStyle('A' . $totalValueRow)->getAlignment()->setHorizontal('right');
        $totalCell->getStyle('A' . $totalValueRow)->getFont()->setBold(true)->setSize(14);
        $worksheet->setCellValueByColumnAndRow(2, $chartColumns + 2, $total);
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $name . '!$C$1', null, 1),
        ];
        $xAxisTickValues  = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $name . '!$A$2:$A$' . ($chartColumns + 1), null, 4),
        ];
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $name . '!$C$2:$C$' . ($chartColumns + 1), null, 4),
        ];

        $series = new DataSeries(
            ($questionType === QuestionType::OPEN_ENDED) ? DataSeries::TYPE_PIECHART : DataSeries::TYPE_BARCHART,
            null,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );

        $layout = new Layout();
        $layout->setShowVal(true);
        $layout->setShowPercent(true);

        $plotArea = new PlotArea($layout, [$series]);

        $legend = new Legend(Legend::POSITION_RIGHT, $layout, true);

        $title = new Title($question['text']);

        $chart = new Chart(
            'chart1',
            $title,
            $legend,
            $plotArea,
            0,
            'gap', // displayBlanksAs,
            null,
            null
        );

        $chart->setTopLeftPosition('A' . ($chartColumns + 3));
        $chart->setBottomRightPosition('H' . (($questionType === QuestionType::OPEN_ENDED) ? $chartColumns + 23 : $chartColumns + 12));

        $openEndedStart = $chartColumns + 23;
        $responses      = [];
        foreach ($keywordResponse as $key => $keyword) {
            $responses[$key] = $keyword[2];
        }

        if ($questionType === QuestionType::OPEN_ENDED) {
            $this->createSamplesTable($worksheet, $responses, $openEndedStart);
        }

        if ($total) {
            $worksheet->addChart($chart);
        }

        foreach ($worksheet->getColumnIterator() as $col) {
            $worksheet->getColumnDimension($col->getColumnIndex())->setAutoSize(true);
        }
    }

    /**
     * Used in pdf and ppt
     * @param $question
     * @param $questionType
     * @return array
     */
    private function createTableData($question, $questionType): array
    {
        $total = count($question['answers']);
        $data  = [];

        switch ($question['type']) {
            case QuestionType::SINGLE_CHOICE:
                $type = "Answer Choices (Single choice only)";
                break;
            case QuestionType::MULTIPLE_CHOICE:
                $type = "Answer Choices (Multiple choice only)";
                break;
            case QuestionType::RANKING:
                $type = "Answer Choices (Ranking only)";
                break;
            case QuestionType::SCALE:
                $range         = $question['scale'];
                $questionRange = $range['from'] . '-' . $range['to'];
                $type          = "Answer Choices (Scaled choice only i.e. $questionRange)";
                break;
            default:
                $type = "Responses (Open ended only)";
        }
        $headers = [$type, 'No. of Respondents', 'Percentage %'];

        if ($questionType === QuestionType::SINGLE_CHOICE || $questionType === QuestionType::MULTIPLE_CHOICE || $questionType === QuestionType::RANKING) {
            $counts = array_count_values($question['answers']);
            if ($question['answers']) {
                foreach ($counts as $key => $value) {
                    $data[] = [$key, $value, ceil(($value / $total) * 100)];
                }
            }
        }

        if ($questionType === QuestionType::SCALE) {
            $counts = array_count_values($question['answers']);
            $range  = $question['scale'];
            if ($question['answers']) {
                for ($scale = $range['from']; $scale <= $range['to']; $scale++) {
                    if (array_key_exists($scale, $counts)) {
                        $data[] = [$scale, $counts[$scale], ceil(($counts[$scale] / $total) * 100)];
                    } else {
                        $data[] = [$scale, 0, 0];
                    }
                }
            }
        }

        if (($questionType === QuestionType::OPEN_ENDED) && $question['answers']) {
            $analyses = $this->getOpenEndedAnalysis($question);

            $keywords = [];
            foreach ($analyses as $key => $analysis) {
                $keywords[$key] = $analysis[0];
            }

            foreach ($keywords as $key => $value) {
                $data[] = [$key, $value, ceil(($value / $total) * 100)];
            }
        }

        return [
            'headers' => $headers,
            'data'    => $data,
        ];
    }

    /**
     * Used in ppt
     * @param $cell
     */
    private function pptQuestionTableBorderChange($cell): void
    {
        // Change border-color: white
        $cell->getBorders()->getBottom()->setColor(new Color(Color::COLOR_WHITE));
        $cell->getBorders()->getLeft()->setColor(new Color(Color::COLOR_WHITE));
        $cell->getBorders()->getTop()->setColor(new Color(Color::COLOR_WHITE));
        $cell->getBorders()->getRight()->setColor(new Color(Color::COLOR_WHITE));
    }

    /**
     * Used in ppt
     * @param $slide
     * @param $tableData
     * @param $questionType
     */
    private function pptQuestionTable($slide, $tableData, $questionType): void
    {
        $header  = $tableData['headers'];
        $answers = $tableData['data'];

        $yValue = $questionType === QuestionType::OPEN_ENDED ? 300 : 450;

        // Table Startif
        // Create a shape (table)
        $shape = $slide->createTableShape(3);
        $shape->setHeight(400);
        $shape->setWidth(800);
        $shape->setOffsetX(80);
        $shape->setOffsetY($yValue);

        // Add row
        $row = $shape->createRow();
        $row->setHeight(20);
        $row->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->setStartColor(new Color('62BAF4'))
            ->setEndColor(new Color('62BAF4'));
        $oCell = $row->nextCell();
        $oCell->setWidth(450);
        $this->pptQuestionTableBorderChange($oCell);

        $oCell->createTextRun($header[0])->getFont()->setBold(true);
        $oCell->getActiveParagraph()->getAlignment()->setMarginLeft(5);
        $oCell = $row->nextCell();
        $oCell->setWidth(200);
        $this->pptQuestionTableBorderChange($oCell);
        $oCell->createTextRun($header[1])->getFont()->setBold(true);
        $oCell->getActiveParagraph()->getAlignment()->setMarginLeft(5);
        $oCell = $row->nextCell();
        $oCell->setWidth(150);
        $this->pptQuestionTableBorderChange($oCell);
        $oCell->createTextRun($header[2])->getFont()->setBold(true);
        $oCell->getActiveParagraph()->getAlignment()->setMarginLeft(1);


        foreach ($answers as $answer) {
            // Add row
            $row = $shape->createRow();
            $row->setHeight(20);
            $row->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->setStartColor(new Color('62BAF4'))
                ->setEndColor(new Color('62BAF4'));
            $oCell = $row->nextCell();
            $oCell->setWidth(450);
            $this->pptQuestionTableBorderChange($oCell);

            $oCell->createTextRun($answer[0])->getFont();
            $oCell->getActiveParagraph()->getAlignment()->setMarginLeft(5);
            $oCell = $row->nextCell();
            $oCell->setWidth(150);
            $this->pptQuestionTableBorderChange($oCell);
            $oCell->createTextRun($answer[1])->getFont();
            $oCell->getActiveParagraph()->getAlignment()->setMarginLeft(5);
            $oCell = $row->nextCell();
            $oCell->setWidth(100);
            $this->pptQuestionTableBorderChange($oCell);
            $oCell->createTextRun($answer[2])->getFont();
            $oCell->getActiveParagraph()->getAlignment()->setMarginLeft(1);
        }
        // Table End
    }

    /**
     * Used in ppt
     * @param $slide
     * @param $questionType
     * @param $chartData
     */
    private function pptQuestionChart($slide, $questionType, $chartData): void
    {
        if ($questionType !== QuestionType::OPEN_ENDED) {
            // Set Style
            $oFill = new Fill();
            $oFill->setFillType(Fill::FILL_SOLID);

            // Generate sample data for chart
            $seriesData = [0];
            foreach ($chartData as $data) {
                $seriesData[$data[0]] = $data[2];
            }
            $dataMaxValue = max($seriesData) + 10;

            if ($questionType === QuestionType::SCALE || $questionType === QuestionType::RANKING) {
                /*
                 * Chart settings and create image
                 */
                $chart = new PieChartGD(700, 400);

                $pieChartColor = [
                    '#007ED6', '#8399EB', '#8E6CEF', '#9C46D0', '#C758D0', '#E01E84', '#FF0000', '#FF7300', '#2A7EB3', '#5086A9',
                    '#FFAF00', '#FFEC00', '#D5F30B', '#52D726', '#1BAA2F', '#2DCB75', '#26D7AE', '#7CDDDD', '#5FB7D4', '#97D9FF',
                ];
                $chart->setTitle('');
                $chart->setOutputQuality(100);
                // Method chaining coming soon!

                foreach ($seriesData as $key => $value) {
                    $chart->addSlice($key, $value, $pieChartColor[$key]);
                }

                $chart->draw();
                $img_path = __DIR__ . '/assets/images/pie.jpg';
                $chart->savePNG($img_path);

                $survey54Logo = $slide->createDrawingShape();
                $survey54Logo->setName('Survey54 logo')
                    ->setDescription('Survey54 logo')
                    ->setPath($img_path)
                    ->setHeight(350)
                    ->setWidth(600)
                    ->setOffsetX(180)
                    ->setOffsetY(80);
            } else {
                // Image dimensions
                $imageWidth  = 750;
                $imageHeight = 450;

                // Grid dimensions and placement within image
                $gridTop    = 40;
                $gridLeft   = 50;
                $gridBottom = 390;
                $gridRight  = 700;
                $gridHeight = $gridBottom - $gridTop;
                $gridWidth  = $gridRight - $gridLeft;

                // Bar and line width
                $lineWidth = 1;
                $barWidth  = 34;

                // Font settings
                $font     = __DIR__ . '/assets/fonts/OpenSans-Regular.ttf';
                $fontSize = 10;

                // Margin between label and axis
                $labelMargin = 20;

                // Max value on y-axis
                $yMaxValue = $dataMaxValue;

                // Distance between grid lines on y-axis
                $yLabelSpan = 10;

                // Init image
                $chart = imagecreate($imageWidth, $imageHeight);

                // Setup colors
                $backgroundColor = imagecolorallocate($chart, 255, 255, 255);
                $axisColor       = imagecolorallocate($chart, 85, 85, 85);
                $labelColor      = $axisColor;
                $gridColor       = imagecolorallocate($chart, 212, 212, 212);
                $barColor        = imagecolorallocate($chart, 47, 133, 217);

                imagefill($chart, 0, 0, $backgroundColor);

                imagesetthickness($chart, $lineWidth);

                /*
                 * Print grid lines bottom up
                 */
                for ($i = 0; $i <= $yMaxValue; $i += $yLabelSpan) {
                    $y = $gridBottom - $i * $gridHeight / $yMaxValue;

                    // draw the line
                    imageline($chart, $gridLeft, $y, $gridRight, $y, $gridColor);

                    // draw right aligned label
                    $labelBox   = imagettfbbox($fontSize, 0, $font, (string)$i);
                    $labelWidth = $labelBox[4] - $labelBox[0];

                    $labelX = $gridLeft - $labelWidth - $labelMargin;
                    $labelY = $y + $fontSize / 2;

                    imagettftext($chart, $fontSize, 0, $labelX, $labelY, $labelColor, $font, (string)$i);
                }

                /*
                 * Draw x- and y-axis
                 */
                imageline($chart, $gridLeft, $gridTop, $gridLeft, $gridBottom, $axisColor);
                imageline($chart, $gridLeft, $gridBottom, $gridRight, $gridBottom, $axisColor);

                /*
                 * Draw the bars with labels
                 */

                $barSpacing = $gridWidth / count($seriesData);
                $itemX      = $gridLeft + $barSpacing / 2;

                foreach ($seriesData as $key => $value) {
                    // Draw the bar
                    $x1 = $itemX - $barWidth / 2;
                    $y1 = $gridBottom - $value / $yMaxValue * $gridHeight;
                    $x2 = $itemX + $barWidth / 2;
                    $y2 = $gridBottom - 1;

                    imagefilledrectangle($chart, $x1, $y1, $x2, $y2, $barColor);

                    // Draw the label
                    $labelBox   = imagettfbbox($fontSize, 0, $font, $key);
                    $labelWidth = $labelBox[4] - $labelBox[0];

                    $labelX = $itemX - $labelWidth / 2;
                    $labelY = $gridBottom + $labelMargin + $fontSize;

                    imagettftext($chart, $fontSize, 0, $labelX, $labelY, $labelColor, $font, $key);

                    $itemX += $barSpacing;
                }

                ob_start();
                imagepng($chart);
                $image_data = ob_get_clean();

                $image_data_base64 = base64_encode($image_data);

                $surveyImage = new Base64();
                $imageData   = "data:image/jpeg;base64," . $image_data_base64;

                $surveyImage->setName('Survey logo')
                    ->setDescription('Survey logo')
                    ->setData($imageData)
                    ->setResizeProportional(false)
                    ->setHeight(350)
                    ->setWidth(650)
                    ->setOffsetX(130)
                    ->setOffsetY(80);
                $slide->addShape($surveyImage);
            }
        }
    }

    /**
     * Used in ppt
     * @param $survey
     * @param $question
     * @param $questionSlide
     */
    private function pptQuestionSlideHeader($survey, $question, $questionSlide): void
    {
        // header question Text
        $questionText = 'Q' . $question['id'] . ' - ' . $question['text'];
        $shape        = $questionSlide->createRichTextShape()
            ->setHeight(50)
            ->setWidth(400)
            ->setOffsetX(60)
            ->setOffsetY(20);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $shape->createTextRun($questionText)->getFont()->setBold(true)->setSize(12);


        // header survey Image
        if ($survey['image']) {
            // Add a file drawing (JPEG) to the slide
            $surveyImage = new Base64();
            $imageData   = "data:image/jpeg;base64," . base64_encode(file_get_contents($survey['image']));

            $surveyImage->setName('Survey logo')
                ->setDescription('Survey logo')
                ->setData($imageData)
                ->setResizeProportional(false)
                ->setHeight(60)
                ->setWidth(160)
                ->setOffsetX(750)
                ->setOffsetY(15);
            $questionSlide->addShape($surveyImage);
        }
    }

    /**
     * Used in ppt
     * @param $slide
     * @param $currentYear
     */
    private function pptFooter($slide, $currentYear): void
    {
        // Footer
        $footerImage = $slide->createDrawingShape();
        $footerImage->setName('Survey54 logo')
            ->setDescription('Survey54 logo')
            ->setPath(__DIR__ . '/assets/images/favicon.png')
            ->setHeight(30)
            ->setOffsetX(10)
            ->setOffsetY(680);

        // Create a shape (text)
        $footerImageText = $slide->createRichTextShape()
            ->setHeight(30)
            ->setWidth(250)
            ->setOffsetX(30)
            ->setOffsetY(680);
        $footerImageText->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $footerImageText->createTextRun('Survey conducted by Survey54')->getFont()->setSize(10);

        // Current Year
        $footerImageText = $slide->createRichTextShape()
            ->setHeight(30)
            ->setWidth(100)
            ->setOffsetX(400)
            ->setOffsetY(680);
        $footerImageText->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $footerImageText->createTextRun('©' . $currentYear)->getFont()->setSize(10);

        // Survey54 URL
        $footerImageText = $slide->createRichTextShape()
            ->setHeight(30)
            ->setWidth(300)
            ->setOffsetX(730)
            ->setOffsetY(680);
        $footerImageText->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $footerImageText->createTextRun('https//www.survey54.com')->getFont()->setSize(10);
    }

    /**
     * @param Worksheet $worksheet
     * @param array $responses
     * @param int $startRow
     */
    private function createSamplesTable(Worksheet $worksheet, array $responses, int $startRow): void
    {
        $worksheet->setCellValue('A' . ($startRow + 2), "Sample responses");
        $row       = $startRow + 4;
        $noteCell  = $row - 1;
        $titleCell = $row - 2;
        $worksheet->getStyle('A' . ($titleCell))->getFont()->setBold(true)->setSize(15);
        $worksheet->setCellValue('A' . ($noteCell), 'Note: these may have false positives, based on context of use, please check list');
        $worksheet->getStyle('A' . ($noteCell))->getFont()->setItalic(true)->setBold(true)->setSize(14);
        foreach ($responses as $key => $value) {
            $value = array_unique($value);
            if (count($value) > 0) {
                $row++;
                $worksheet->setCellValue('A' . $row, $key);
                $worksheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
                foreach ($value as $key2 => $val) {
                    $row++;
                    $worksheet->setCellValue('A' . $row, ' - ' . $val);
                }
                $row++;
            }
        }
    }

    /**
     * @param array $question
     * @return array
     */
    public function getOpenEndedAnalysis(array $question): array
    {
        $keys = [];

        $answers = $question['answers'] ?? [];
        $options = $question['keywords'] ?? [];

        foreach ($options as $option) {
            $option                = strtolower($option);
            $optionWithOnlyLetters = [];
            preg_match_all('/\w+/', $option, $optionWithOnlyLetters);

            $optionWithOnlyLetters = implode(' ', $optionWithOnlyLetters[0]);

            foreach ($answers as $answer) {
                $answerWithOnlyLetters = [];
                preg_match_all('/\w+/', $answer, $answerWithOnlyLetters);

                $answerWithOnlyLetters = implode(' ', $answerWithOnlyLetters[0]);
                if (str_contains(strtolower($answerWithOnlyLetters), $optionWithOnlyLetters)) {
                    if (!isset($keys[ucfirst($option)])) {
                        $keys[ucfirst($option)][] = 0;
                        $keys[ucfirst($option)][] = [];
                        $keys[ucfirst($option)][] = [];
                    }
                    $keys[ucfirst($option)][0]++;
                    $keys[ucfirst($option)][1][] = $answer;
                    if (count($keys[ucfirst($option)][2]) < 5) {
                        $keys[ucfirst($option)][2][] = $answer;
                    }
                }
            }
        }

        return $keys;
    }
}
