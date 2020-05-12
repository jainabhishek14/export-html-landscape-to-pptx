<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    

    define('CLI', (PHP_SAPI == 'cli') ? true : false);
    define('EOL', CLI ? PHP_EOL : '<br />');
    define('SCRIPT_FILENAME', basename($_SERVER['SCRIPT_FILENAME'], '.php'));
    define('IS_INDEX', SCRIPT_FILENAME == 'index');
    define('TEMPLATE_DIR', dirname(__DIR__).DIRECTORY_SEPARATOR.getenv("ODP_DIR").DIRECTORY_SEPARATOR.$templateName.DIRECTORY_SEPARATOR);
    
    use PhpOffice\PhpPresentation\PhpPresentation;
    use PhpOffice\PhpPresentation\IOFactory;
    use PhpOffice\PhpPresentation\Autoloader;
    use PhpOffice\PhpPresentation\Slide;
    use PhpOffice\PhpPresentation\Style\Color;
    use PhpOffice\PhpPresentation\Style\Border;
    use PhpOffice\PhpPresentation\Style\Alignment;
    use PhpOffice\PhpPresentation\Style\Fill;
    use PhpOffice\PhpPresentation\Shape\Drawing\File;
    use PhpOffice\PhpPresentation\Shape\RichText;

    include_once(TEMPLATE_DIR."config.php");
    
    // Set writers
    $writers = array('PowerPoint2007' => 'pptx', 'ODPresentation' => 'odp');

    // echo date('H:i:s') . ' Create new PHPPresentation object'.EOL;
    $objPHPPresentation = new PhpPresentation();
    /*these are different place holders that hold the content that need to be replaced in content.xml*/

    $processedDir = getenv("PROCESSED_FILES_DIR");

    $response = array(
        "type" => "error",
        "message" => ""
    );
    // do some checks to make sure the outputs are set correctly.
    if (is_dir(dirname(__DIR__).DIRECTORY_SEPARATOR.$processedDir) === false) {
        $response['message'] = 'The output folder is not present!';
        $logger->error($response);
        exit(json_encode($response));
    }
    if (is_writable(dirname(__DIR__).DIRECTORY_SEPARATOR.$processedDir.DIRECTORY_SEPARATOR) === false) {
        $response['message'] = 'The output folder is not writable!';
        $logger->error($response);
        exit(json_encode($response));
    }

    $content =  $data;
    $title = (isset($content->landscapeName)) ? $content->landscapeName : $filename;

    // echo date('H:i:s') . ' Set properties'.EOL;
    $objPHPPresentation->getDocumentProperties()
        ->setCreator(getenv('CREATOR'))
        ->setLastModifiedBy(getenv('CREATOR'))
        ->setTitle($title)
        ->setSubject($title);


    // Remove first slide
    // echo date('H:i:s') . ' Remove first slide'.EOL;
    
    $objPHPPresentation->removeSlideByIndex(0);
    $slides = createSlides($objPHPPresentation, $content, $config);

    $filename = $filename."_".date("Y_m_d_H_i_s");
    write($objPHPPresentation, $filename, ($format === "pdf") ? "pptx" : $format, $writers);
    
    if ($format === "pdf") {
        convertToPDF($filename);
    }

    $filename = $filename.'.'.$format;
    // echo dirname(__DIR__). DIRECTORY_SEPARATOR.getenv("PROCESSED_FILES_DIR").DIRECTORY_SEPARATOR.$filename;
    // Add Headers so that we can download the file that is created
    if (!is_file(dirname(__DIR__). DIRECTORY_SEPARATOR.getenv("PROCESSED_FILES_DIR").DIRECTORY_SEPARATOR.$filename)) {
        $response['message'] = 'File not generated. Please contact the authorised person.';
        exit(json_encode($response));
    }
    
    switch ($responseType) {
        case 'filepath':
            exit(json_encode(
                array(
                'status'=>true,
                'url'=> getenv("PROCESSED_FILES_DIR").DIRECTORY_SEPARATOR.$filename)
            ));
            break;
        case 'stream':
            header("Content-Disposition: attachment; filename=\"".$filename."\"");
            header("Content-Type: ". 'application/vnd.oasis.opendocument.presentation');
            header("Content-Length: " . filesize(dirname(__DIR__). DIRECTORY_SEPARATOR.getenv("PROCESSED_FILES_DIR").DIRECTORY_SEPARATOR.$filename));

             # required for IE - so that it doesn't cache the information/file
            header("Pragma: public");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

            $fp = fopen(dirname(__DIR__). DIRECTORY_SEPARATOR.getenv("PROCESSED_FILES_DIR").DIRECTORY_SEPARATOR.$filename, "r");
            while (!feof($fp)) {
                echo fread($fp, 65536);
                flush(); // this is essential for large downloads
            }
            fclose($fp);
            break;
    }

    function convertToPDF($filename)
    {
        $cmd = dirname(__DIR__).DIRECTORY_SEPARATOR.getenv("CONVERSION_BINARY_PATH")." --headless --convert-to pdf --outdir ".getenv("PROCESSED_FILES_DIR")." ".getenv("PROCESSED_FILES_DIR").DIRECTORY_SEPARATOR."{$filename}.pptx";
        exec($cmd, $output, $return);
        file_put_contents('/tmp/output.php', implode("\n", $output)."\n", FILE_APPEND);
        return true;
    }

    function getAssetBackgroundColor($colorsList)
    {
        $list = array_values((array) $colorsList);
        return $list[0];
    }

    function calculateRequiredColumns($data, $config)
    {
        $maxRow = $config['numRowsPerPage'];
        $phaseInfo = [];
        $phase = $data[0]->phase;
        $count = 0;
        foreach ($data as $key => $value) {
            if ($value->phase===$phase) {
                $count++;
            } else {
                $phase = $value->phase;
                $count = 1;
            }
            $phaseInfo[$phase] = ceil($count/$maxRow);
        }
        return $phaseInfo;
    }


    function calculatePhaseWidth($phaseInfo)
    {
        $indexCount = 0;
        $maxColumns = 7;
        
        foreach ($phaseInfo as $key => $value) {
            $indexCount++;
            if (count($phaseInfo) == $indexCount) {
                $phaseInfo[$key] = $maxColumns;
            } else {
                $maxColumns = $maxColumns - $value;
            }
        }
        return $phaseInfo;
    }

    /**
     * creates front page
     * @param  PhpOffice\PhpPresentation\PhpPresentation $objPHPPresentation [description]
     * @param  string                    $title              [description]
     * @param  string                    $altTitle           [description]
     * @return PhpOffice\PhpPresentation\Slide               [description]
     */
    function createFrontPage(
        PhpOffice\PhpPresentation\PhpPresentation $objPHPPresentation,
        string $title = "Competitive Landscape",
        string $altTitle = ""
    ) {
        // Create slide
        $slide = $objPHPPresentation->createSlide();
        $currentSlide = createTemplatedSlide($slide); // local function

        // echo date('H:i:s') . ' Create a shape (Front Page Image)'.EOL;
        $shape = $currentSlide->createDrawingShape();
        $shape->setName('Home Page Image')
            ->setPath(TEMPLATE_DIR.getenv('PICTURES_DIR').DIRECTORY_SEPARATOR.getenv('HOMEPAGE_IMAGE_NAME'))
            ->setHeight(1100)
            ->setWidth(900)
            ->setOffsetX(30)
            ->setOffsetY(50);

        // echo date('H:i:s') . ' Create a shape (Client Logo)'.EOL;
        $clientLogo = $currentSlide->createDrawingShape();
        $clientLogo->setName('Client Logo')
            ->setPath(TEMPLATE_DIR.getenv('PICTURES_DIR').DIRECTORY_SEPARATOR.getenv('CLIENT_LOGO'))
            ->setHeight(75)
            ->setWidth(200)
            ->setOffsetX(30)
            ->setOffsetY(575);

        // echo date('H:i:s') . ' Create a shape (Presentation Title)'.EOL;
        $presentationTitleContainer = $currentSlide->createRichTextShape();
        $presentationTitleContainer
            ->setHeight(175)
            ->setWidth(960)
            ->setOffsetX(0)
            ->setOffsetY(300)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->setEndColor(new Color('bb94d8eb3'))
            ->setStartColor(new Color('bb4d8eb3'));


        $presentationTitle = $currentSlide->createRichTextShape();
        $presentationTitle
          ->setHeight(100)
          ->setWidth(960)
          ->setOffsetX(2)
          ->setOffsetY(340);
        $presentationTitle->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

        $textRun = $presentationTitle->createTextRun($title);
        $textRun
            ->getFont()
            ->setBold(true)
            ->setSize(30)
            ->setColor(new Color('ffffffff'));


        if ($altTitle !== "") {
            $presentationAltTitle = $currentSlide->createRichTextShape();
            $presentationAltTitle
            ->setHeight(50)
            ->setWidth(960)
            ->setOffsetX(2)
            ->setOffsetY(410);
            $presentationAltTitle->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);


            
            $alternateText = $presentationAltTitle->createTextRun($altTitle);
            $alternateText
                ->getFont()
                ->setBold(false)
                ->setSize(18)
                ->setColor(new Color('ffffffff'));
        }
        

        return $currentSlide;
    }

    /**
     * Creates a templated slide
     *
     * @param \PhpOffice\PHPPresentation\Slide $slide
     * @return \PhpOffice\PhpPresentation\Slide
     */
    function createTemplatedSlide(PhpOffice\PhpPresentation\Slide $slide)
    {
        // echo date('H:i:s') . ' Create templated slide'.EOL;

        // echo date('H:i:s') . ' Create a shape (footer bar)'.EOL;
        $footerBar = $slide->createRichTextShape();
        $footerBar
            ->setHeight(40)
            ->setWidth(960)
            ->setOffsetX(0)
            ->setOffsetY(675)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setEndColor(new Color('FF6a737d'))
            ->setStartColor(new Color('FF6a737d'));
            
        // Add logo
        // echo date('H:i:s') . ' Create a shape (application logo)'.EOL;
        $shape = $slide->createDrawingShape();
        $shape->setName('Template logo')
            ->setPath(TEMPLATE_DIR.getenv('PICTURES_DIR').DIRECTORY_SEPARATOR.getenv('APPLICATION_LOGO'))
            ->setHeight(30)
            ->setOffsetX(800)
            ->setOffsetY(675);
        $shape->getShadow()->setVisible(true)
            ->setDirection(45)
            ->setDistance(10);
        $shape->getHyperlink()->setUrl('https://integratedge.com')->setTooltip('Integrate Edge');

        // echo date('H:i:s') . ' Create a shape (rich text)'.EOL;
        $footerText = $slide->createRichTextShape();
        $footerText
          ->setHeight(30)
          ->setWidth(600)
          ->setOffsetX(10)
          ->setOffsetY(680);
        $footerText->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $textRun = $footerText->createTextRun('© 2012-'.date("Y").' WNS Global Services Pvt. Ltd. All rights reserved.');
        $textRun
            ->getFont()
            ->setBold(true)
            ->setSize(10)
            ->setColor(new Color('FFFFFFFF'));
        
        // Return slide
        return $slide;
    }

    /**
     * Write documents
     *
     * @param \PhpOffice\PhpPresentation\PhpPresentation $phpPresentation
     * @param string $filename
     * @param array $writers
     * @return string
     */
    function write($phpPresentation, $filename, $format, $writers)
    {
        $result = '';
        // Write documents
        foreach ($writers as $writer => $extension) {
            if (!is_null($extension) && $extension === $format) {
                $result .= date('H:i:s') . " Write to {$writer} format";
                $xmlWriter = IOFactory::createWriter($phpPresentation, $writer);
                $xmlWriter->save(dirname(__DIR__). DIRECTORY_SEPARATOR.getenv("PROCESSED_FILES_DIR").DIRECTORY_SEPARATOR."{$filename}.{$extension}");
            }
            $result .= EOL;
        }
        //$result .= getEndingNotes($writers);
        // return $result;
        return true;
    }

    function createSlides(PhpOffice\PhpPresentation\PhpPresentation $objPHPPresentation, stdClass $data, array $config)
    {
        $phaseData = $data->phases;
        $legends = $data->legends->colors;
        $icons = $data->legends->icons;

        $sno = 0;              //this is slide number.
        $slides = array();    // 2d array to have elements, here index is the slide no
        $prevPhase = "";
        $spaceRemainingInCurrentSlide = $config['numAssetsPerPage'];
        $numColumnsRemainingInSlide = floor($config['numAssetsPerPage'] / $config['numRowsPerPage']);
        foreach ($phaseData as $phaseName => $phase) {
            $slideAssets = array();
            $elementsInPhase = count($phase->assets);
            $currentPhase = $phaseName;

            foreach ($phase->assets as $key => $asset) {
                if ((($key + 1) % $config['numRowsPerPage'] === 1 && !$numColumnsRemainingInSlide) || !$spaceRemainingInCurrentSlide) {
                    $sno++;
                    $spaceRemainingInCurrentSlide = $config['numAssetsPerPage'];
                    $numColumnsRemainingInSlide = floor($config['numAssetsPerPage'] / $config['numRowsPerPage']);
                }
                

                $asset->phase = $phaseName;
                $slides[$sno][] = $asset;
                $elementsInPhase--;
                $spaceRemainingInCurrentSlide--;
                $prevPhase = $currentPhase;
                if (($key + 1) % $config['numRowsPerPage'] === 1) {
                    $numColumnsRemainingInSlide--;
                }
            }
        }
        
        // make sheet title dynamic by anil kumar on 11 March 2019 issue #390
        $frontSlide =  createFrontPage($objPHPPresentation, (isset($data->sheetname) && !empty($data->sheetName)) ? $data->sheetName : "Competitive Landscape", (isset($data->altTitle) && !empty($data->altTitle)) ? $data->altTitle : "");
        foreach ($slides as $slide) {
            $pptSlide = createLandscapeSlide($objPHPPresentation, $slide, $config, $data->displayKey, $data->colorsKey, $data->iconsKey, $data->landscapeName, (isset($data->show_legends) ? $data->show_legends : 'Y'), (isset($data->show_symbols) ? $data->show_symbols : 'Y'), (isset($data->show_flag) ? $data->show_flag : 'Y'), (isset($data->has_phase_arrow) && strtolower($data->has_phase_arrow) === "n" ? $data->has_phase_arrow : "Y"));
        }
        return $frontSlide;
    }

    function createPhaseTriangle(
        PhpOffice\PhpPresentation\Slide $slide,
        int $xCoordinate,
        int $yCoordinate,
        int $width,
        int $height,
        int $rotation,
        string $color
    ) {
        $triangle1 = $slide->createRichTextShape();
        $triangle1
            ->setOffsetX($xCoordinate)
            ->setOffsetY($yCoordinate)
            ->setWidth($width)
            ->setHeight($height)
            ->setRotation($rotation)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->setEndColor(new Color($color))
            ->setStartColor(new Color($color));
        $triangle1
            ->getBorder()
            ->setLineStyle(Border::LINE_SINGLE)
            ->setColor(new Color($color));
        return $triangle1;
    }

    function renderPhase(
        PhpOffice\PhpPresentation\Slide $slide,
        int $width,
        int $xCoordinate,
        int $yCoordinate,
        string $color,
        array $config,
        string $title = "Random Phase",
        string $hasArrow = "Y"
    ) {
        $arrowUpperPartXCoordinate = $xCoordinate + $width - 9;
        $arrowUpperPartYCoordinate = $yCoordinate + 5;
        $arrowUpperPartLength = $config['phaseBarHeight'];
        
        //$phaseTriangleWhite = createPhaseTriangle($slide, $arrowUpperPartXCoordinate -100, $arrowUpperPartYCoordinate, 33, 33, 45, "FFFFFFFF");
        
        $phaseElement = $slide->createRichTextShape();
        $phaseElement
            ->setHeight($config['phaseBarHeight'])
            ->setWidth($width)
            ->setOffsetX($xCoordinate)
            ->setOffsetY($yCoordinate)
            ->setAutoFit(RichText::AUTOFIT_NORMAL, 90, 20)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->setEndColor(new Color($color))
            ->setStartColor(new Color($color));

        $phaseElement->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $textRun = $phaseElement->createTextRun(html_entity_decode($title));
        $textRun
            ->getFont()
            ->setBold(true)
            ->setSize(12)
            ->setColor(new Color('FFFFFFFF'));
        if (strtolower($hasArrow) === "y") {
            $phaseTriangle = createPhaseTriangle($slide, $arrowUpperPartXCoordinate, $arrowUpperPartYCoordinate, 20, 20, 45, $color);
        }
    }

    function renderPhaseBar(
        PhpOffice\PhpPresentation\Slide $slide,
        array $data,
        array $config,
        int $initialXCoordinate = 0,
        int $initialYCoordinate = 0,
        string $hasArrow = "Y",
        string $hasFlags = "Y"
    ) {
        // $config['phaseBarHeight'] = (strtolower($hasFlags) === 'y') ? $config['phaseBarHeight'] : $config['phaseBarHeight'] + 20;
        $initialXCoordinate = ($initialXCoordinate) ? $initialXCoordinate : $config['initialXCoordinate'];
        $initialYCoordinate = ($initialYCoordinate) ? $initialYCoordinate : $config['initialYCoordinateOfAssets'] + $config['phaseBarHeight'] + $config['spaceBetweenRows'];
        $phaseBar = $slide->createRichTextShape();
        $phaseBar
            ->setHeight($config['phaseBarHeight'])
            ->setWidth(940)
            ->setOffsetX($initialXCoordinate)
            ->setOffsetY($initialYCoordinate);
        
        $phaseInfo = calculatePhaseWidth(calculateRequiredColumns($data, $config));
        $flag3 = false;
        $prevXCoordinate = $initialXCoordinate;
        foreach ($phaseInfo as $key => $value) {
            $space = strtolower($hasArrow) === "y" ? (($value -1) * $config['spaceBetweenColumns']) : (($value -1) * $config['spaceBetweenColumns']);
            $barWidth = $value * $config['columnWidth'] + $space;
            $barXCoordinate = $flag3 ? $prevXCoordinate  : $initialXCoordinate;
            $prevXCoordinate = $barXCoordinate + $barWidth + $config['spaceBetweenColumns'];
            renderPhase($slide, (strtolower($hasArrow) === "y") ? $barWidth - 20 : $barWidth, $barXCoordinate, $initialYCoordinate, "FF6a737d", $config, $key, $hasArrow);
            $flag3 = true;
        }
        return $phaseBar;
    }

    function getAssetDisplayInformation(
        stdClass $asset,
        array $displayKey
    ) {
        $previousText = $text = "";
        foreach ($displayKey as $key => $value) {
            $text .= ((is_array($asset->$value)) ? html_entity_decode($asset->$value[0]) : html_entity_decode($asset->$value)) ."\n";
        }
        return $text;
    }

    function calculateIconPosition(
        int $xCoordinate,
        int $yCoordinate,
        int $height,
        int $width,
        string $position
    ) : array {
        switch ($position) {
            case 'bottom-left':
                $yCoordinate = $yCoordinate + $height + 10;
                $xCoordinate = $xCoordinate - 10;
                break;
            case 'bottom-right':
                $xCoordinate = $xCoordinate + $width - 20;
                $yCoordinate = $yCoordinate + $height + 10;
                break;
            case 'top-right':
                $xCoordinate = $xCoordinate + $width - 20;
                $yCoordinate = $yCoordinate - 10;
                break;
            case 'top-left':
                $xCoordinate = $xCoordinate - 10;
                $yCoordinate = $yCoordinate - 10;
                break;
            default:
                //as is
        }
        return compact("xCoordinate", "yCoordinate");
    }

    function renderIcon(
        PhpOffice\PhpPresentation\Slide $slide,
        stdClass $icon,
        int $xCoordinate,
        int $yCoordinate,
        int $height,
        int $width
    ) {
        $coordinates = calculateIconPosition($xCoordinate, $yCoordinate, $height, $width, $icon->position);
        $iconBox = renderLegendIconBoxContainer($slide, $icon->name, $coordinates['xCoordinate'], $coordinates['yCoordinate'], checkExistenceOfFile($icon->iconPath, $icon->name, $icon->iconPath), 20);
        return $slide;
    }

    /**
     * responsible for rendering asset element
     * @param  PhpOffice\PhpPresentation\Slide $slide
     * @param  array                           $asset
     * @param  array                           $config
     * @param  int                             $xCoordinate
     * @param  int                             $yCoordinate
     * @param  array                           $displayKey
     * @param  array                           $iconsKey
     * @return PhpOffice\PhpPresentation\Slide
     */

    function renderAsset(
        PhpOffice\PhpPresentation\Slide $slide,
        stdClass $asset,
        array $config,
        int $xCoordinate,
        int $yCoordinate,
        array $displayKey,
        array $iconsKey,
        string $showLegends,
        string $showSymbols,
        string $showFlags
    ) : PhpOffice\PhpPresentation\Slide {
        $assetColor = str_replace("#", "", getAssetBackgroundColor($asset->colors));
        $assetContainer = $slide->createRichTextShape();
        
        if ($showFlags === "Y") {
            $assetYCoordinate = $yCoordinate - $config['bottomBarHeight'];
        } else {
            $assetYCoordinate = $yCoordinate - $config['bottomBarHeight'];
        }
        

        $assetContainer
            ->setHeight($config['rowHeight'])
            ->setWidth($config['columnWidth'])
            ->setOffsetX($xCoordinate)
            ->setOffsetY($assetYCoordinate)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->setEndColor(new Color("FF".$assetColor))
            ->setStartColor(new Color("FF".$assetColor));

        
        if ($showFlags === 'Y') {
            $assetBottomBarContainer = $slide->createRichTextShape();
            $assetBottomBarContainer
                ->setHeight($config['bottomBarHeight'])
                ->setWidth($config['columnWidth'])
                ->setOffsetX($xCoordinate)
                ->setOffsetY($yCoordinate - $config['bottomBarHeight'] + $config['rowHeight'])
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->setEndColor(new Color("FFFFFFFF"))
                ->setStartColor(new Color("FFFFFFFF"));
            $assetBottomBarContainer
                ->getBorder()
                ->setLineStyle(Border::LINE_SINGLE)
                ->setLineWidth(0.5)
                ->getColor()->setARGB('FFCCCCCC');

            $assetBottomBarContainer
                ->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $assetBottomBarContainerColumn = 0;
        }
        //echo $yCoordinate - $config['bottomBarHeight'] + $config['rowHeight'];
        if ($showSymbols === 'Y' || $showFlags === 'Y') {
            foreach ($iconsKey as $iKey) {
                if (isset($asset->$iKey) && is_array($asset->$iKey)) {
                    foreach ($asset->$iKey as $icon) {
                        if (isset($icon->name)) {
                            if ($icon->position === "bottom-bar") {
                                $yCoordinateIcon = $assetBottomBarContainer->getOffsetY() + 5;
                                $xCoordinateIcon = $assetBottomBarContainer->getOffsetX() + 5 + ($assetBottomBarContainerColumn * 35);
                                $assetBottomBarContainerColumn++;
                            } else {
                                $yCoordinateIcon = $yCoordinate - $config['bottomBarHeight'];
                                $xCoordinateIcon = $xCoordinate;
                            }
                            $iconElement = renderIcon($slide, $icon, $xCoordinateIcon, $yCoordinateIcon, $config['rowHeight'], $config['columnWidth']);
                        }
                    }
                }
            }
        }

        $assetText = $slide->createRichTextShape();
        $assetText
            ->setHeight($config['rowHeight'])
            ->setWidth($config['columnWidth'])
            ->setOffsetX($xCoordinate)
            ->setOffsetY($assetYCoordinate)
            ->setAutoFit(RichText::AUTOFIT_NORMAL, 90, 10);

        $assetText->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $textRun = $assetText->createTextRun(html_entity_decode(getAssetDisplayInformation($asset, $displayKey)));
        $textRun
            ->getFont()
            ->setName("Arial")
            // ->setBold(!array_search($value, $displayKey))
            ->setSize(10)
            ->setColor(new Color('FFFFFFFF'));
        return $slide;
    }

    function renderAssets(PhpOffice\PhpPresentation\Slide $slide, array $data, array $config, array $displayKey, array $iconsKey, string $showLegends, string $showSymbols, string $showFlags)
    {
        $prevPhase = "";
        $xCoordinate = $config['initialXCoordinate'];
        $yCoordinate = $config['initialYCoordinateOfAssets'];
        $column = 0;
        $columnElements = 0;
        foreach ($data as $asset) {
            if ($prevPhase === "" || $prevPhase !== $asset->phase || $columnElements === $config['numRowsPerPage']) {
                //next Column; means xCoordinate increases
                $yCoordinate = $config['initialYCoordinateOfAssets'];
                $xCoordinate =  $column * ($config['columnWidth'] + $config['spaceBetweenColumns']) + $config['initialXCoordinate'];
                ;
                $column++;
                $columnElements = 1;
                $prevPhase = $asset->phase;
            } else {
                //Same Column; means y Coordinate decreases
                if ($showFlags === 'Y') {
                    $yCoordinate -= $config['rowHeight'] + $config['spaceBetweenRows'] + $config['bottomBarHeight'];
                } else {
                    $yCoordinate -= $config['rowHeight'] + $config['spaceBetweenRows'];
                }
                $columnElements++;
            }
            $asset = renderAsset($slide, $asset, $config, $xCoordinate, $yCoordinate, $displayKey, $iconsKey, $showLegends, $showSymbols, $showFlags);
        }
        return $slide;
    }

    /**
     * return true if string1 > string 2
     * @param  string $a [description]
     * @param  string $b [description]
     * @return bool
     */
    function cmp(string $a, string $b) : bool
    {
        return strlen($a) > strlen($b);
    }

    function renderLegendIconBoxContainer(
        PhpOffice\PhpPresentation\Slide $slide,
        string $legendName,
        int $xCoordinate,
        int $yCoordinate,
        string $filePath,
        int $boxSize
    ) {
        $icon = $slide->createDrawingShape();
        $icon
            ->setName($legendName)
            ->setPath($filePath)
            ->setHeight($boxSize)
            ->setOffsetX($xCoordinate)
            ->setOffsetY($yCoordinate);
        return $icon;
    }

    function renderLegendColorBoxContainer(
        PhpOffice\PhpPresentation\Slide $slide,
        string $legendName,
        string $legendValue,
        int $xCoordinate,
        int $yCoordinate,
        int $boxSize
    ) {
        $legendColorBoxContainer = $slide->createRichTextShape();
        $legendColorBoxContainer
            ->setHeight($boxSize)
            ->setWidth($boxSize)
            ->setOffsetX($xCoordinate)
            ->setOffsetY($yCoordinate)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->setEndColor(new Color("FF".$legendValue))
            ->setStartColor(new Color("FF".$legendValue));
        return $legendColorBoxContainer;
    }

    function renderLegendText(
        PhpOffice\PhpPresentation\Slide $slide,
        string $legendName,
        int $width,
        int $xCoordinate,
        int $yCoordinate,
        int $boxSize
    ) {
        $legendColorContainer = $slide->createRichTextShape();
        $legendColorContainer
            ->setHeight($boxSize)
            ->setWidth($width)
            ->setOffsetX($xCoordinate + $boxSize + 5)
            ->setOffsetY($yCoordinate);

        $legendColorContainer->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $legendColorText = $legendColorContainer->createTextRun(html_entity_decode($legendName));
        $legendColorText
            ->getFont()
            ->setName("Arial")
            ->setSize(8);
        return $legendColorText;
    }

    /**
     * render legend item
     * @param  PhpOffice\PhpPresentation\Slide $slide       [description]
     * @param  string                          $legendName  [description]
     * @param  string                          $legendValue [description]
     * @param  int                             $xCoordinate [description]
     * @param  int                             $yCoordinate [description]
     * @param  int                             $width       [description]
     * @param  bool|boolean                    $isFile      [description]
     * @param  string|null                     $filePath    [description]
     * @param  int|integer                     $boxSize     [description]
     * @return PhpOffice\PhpPresentation\Slides
     */
    function renderLegend(
        PhpOffice\PhpPresentation\Slide $slide,
        string $legendName,
        string $legendValue,
        int $xCoordinate,
        int $yCoordinate,
        int $width,
        bool $isFile = false,
        string $filePath = null,
        int $boxSize = 15
    ) : PhpOffice\PhpPresentation\Slide {
        if ($isFile && !empty($filePath)) {
            $iconBox = renderLegendIconBoxContainer($slide, $legendName, $xCoordinate, $yCoordinate, $filePath, $boxSize);
        } else {
            $legendValue = str_replace("#", "", $legendValue);
            $colorBox = renderLegendColorBoxContainer($slide, $legendName, $legendValue, $xCoordinate, $yCoordinate, $boxSize);
        }

        $text = renderLegendText($slide, $legendName, $width, $xCoordinate, $yCoordinate, $boxSize);

        return $slide;
    }

    /**
     * check file existence else download the file
     * @param  string $url      [description]
     * @param  string $fileName [description]
     * @return string           [description]
     */
    function checkExistenceOfFile(string $url, string $fileName) : string
    {
        $filePath = TEMPLATE_DIR.getenv('PICTURES_DIR').DIRECTORY_SEPARATOR.strtolower($fileName).".png";
        if (!file_exists($filePath)) {
            file_put_contents($filePath, file_get_contents("https://".$url));
        }
        return $filePath;
    }

    /**
     * responsible for creating legend section
     * @param  PhpOffice\PhpPresentation\Slide $slide   [description]
     * @param  array                           $legends [description]
     * @param  array                           $config  [description]
     * @return PhpOffice\PhpPresentation\Slide
     */
    function createLegendSection(
        PhpOffice\PhpPresentation\Slide $slide,
        array $legends,
        array $config,
        int $initialXCoordinate = 0,
        int $initialYCoordinate = 0
    ) : PhpOffice\PhpPresentation\Slide {
        uksort($legends, "cmp");                           // sorting the moa by length
        
        $initialXCoordinate = ($initialXCoordinate) ? $initialXCoordinate : $config['initialXCoordinate'];
        $initialYCoordinate = ($initialYCoordinate) ? $initialYCoordinate : $config['initialYCoordinateOfAssets'] + $config['bottomBarHeight'] + $config['phaseBarHeight'] + ($config['spaceBetweenRows'] * 3);
        $yCoordinate = $initialYCoordinate;
        $numLegendsInCurrentRow = 0;
        foreach ($legends as $legendName => $legendValue) {
            if ($numLegendsInCurrentRow !== 0 && $numLegendsInCurrentRow % $config['numLegendsPerRow'] === 0) {
                // New Row; means increase yCoordinate and reset xCoordinate
                $numLegendsInCurrentRow = 1;
                $xCoordinate = $initialXCoordinate;
                $yCoordinate = $yCoordinate + ($config['spaceBetweenRows'] * 2);
            } else {
                // Same Row; means shift xCoordinate
                $xCoordinate = $initialXCoordinate + ($numLegendsInCurrentRow * (940 / $config['numLegendsPerRow']));
                $numLegendsInCurrentRow++;
            }
            if (preg_match('/^#[a-f0-9]{6}$/i', $legendValue)) {
                $legend = renderLegend($slide, $legendName, $legendValue, $xCoordinate, $yCoordinate, (940 / $config['numLegendsPerRow']) - 25);
            } else {
                $filePath = checkExistenceOfFile($legendValue, $legendName);
                $legend = renderLegend($slide, $legendName, $legendValue, $xCoordinate, $yCoordinate, (940 / $config['numLegendsPerRow']) - 25, true, $filePath);
            }
        }
        return $slide;
    }

    /**
     * responsible for accumulating legend data as per slide data
     * @param  PhpOffice\PhpPresentation\Slide $slide     [description]
     * @param  array                           $data      [description]
     * @param  array                           $config    [description]
     * @param  array                           $colorsKey [description]
     * @param  array                           $iconsKey  [description]
     * @return PhpOffice\PhpPresentation\Slide
     */
    function createLegends(
        PhpOffice\PhpPresentation\Slide $slide,
        array $data,
        array $config,
        array $colorsKey,
        array $iconsKey,
        int $initialXCoordinate = 0,
        int $initialYCoordinate = 0,
        string $showLegends = "Y",
        string $showSymbols = "Y",
        string $showFlags = "Y"

    ) : PhpOffice\PhpPresentation\Slide {
        $legends = array();
        foreach ($data as $asset) {
            $legends = array_merge($legends, (array) $asset->colors);
            if ($showSymbols === "Y" || $showFlags === "Y") {
                foreach ($iconsKey as $iKey) {
                    if (isset($asset->$iKey)) {
                        if (is_array($asset->$iKey)) {
                            foreach ($asset->$iKey as $icon) {
                                if (isset($icon->name)) {
                                    $legends[$icon->name] = (isset($icon->iconPath)) ? $icon->iconPath : $icon->name;
                                }
                            }
                        }
                    }
                }
            }
        }
        $legendSectionContent = createlegendSection($slide, $legends, $config, $initialXCoordinate, $initialYCoordinate);
        return $slide;
    }

    /**
     * creates Slides which contains landscape data
     * @param  PhpOffice\PhpPresentation\PhpPresentation $objPHPPresentation [description]
     * @param  array                                     $data               [description]
     * @param  array                                     $config             [description]
     * @param  array                                     $displayKey         [description]
     * @param  array                                     $colorsKey          [description]
     * @param  array                                     $iconsKey           [description]
     * @param  string                                    $title              [description]
     * @return PhpOffice\PhpPresentation\Slide
     */
    function createLandscapeSlide(
        PhpOffice\PhpPresentation\PhpPresentation $objPHPPresentation,
        array $data,
        array $config,
        array $displayKey,
        array $colorsKey,
        array $iconsKey,
        string $title = "Slide Title",
        string $showLegends = "Y",
        string $showSymbols = "Y",
        string $showFlags = "Y",
        string $hasPhaseArrow = "Y"
    ) : PhpOffice\PhpPresentation\Slide {
        // Create slide
        $slide = $objPHPPresentation->createSlide();
        $currentSlide = createTemplatedSlide($slide); // local function


        // echo date('H:i:s') . ' Create a shape (Slide Title)'.EOL;
        $slideTitleContainer = $currentSlide->createRichTextShape();
        $slideTitleContainer
            ->setHeight(80)
            ->setWidth(960)
            ->setOffsetY(10);


        $slideTitle = $currentSlide->createRichTextShape();
        $slideTitle
          ->setHeight(80)
          ->setWidth(960)
          ->setOffsetY(10);
        $slideTitle->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $textRun = $slideTitle->createTextRun($title);
        $textRun
            ->getFont()
            ->setBold(true)
            ->setSize(30)
            ->setColor(new Color('FF000000'));

        $partitionYCoordinate =  $config['initialYCoordinateOfAssets'] + $config['phaseBarHeight'] + $config['rowHeight'] + ($config['spaceBetweenRows'] * 2);

        $partitionAssetsAndLegends = $currentSlide->createLineShape($config['initialXCoordinate'], $partitionYCoordinate, 940, $partitionYCoordinate);
        if (strtolower($showFlags) !== 'y') {
            $config['phaseBarHeight'] += 20;
        }
        $phaseBar = renderPhaseBar($currentSlide, $data, $config, $config['initialXCoordinate'], $partitionYCoordinate - $config['spaceBetweenRows'] - $config['phaseBarHeight'], $hasPhaseArrow, $showFlags);

        $assets = renderAssets($currentSlide, $data, $config, $displayKey, $iconsKey, $showLegends, $showSymbols, $showFlags);

        $legends = createLegends($currentSlide, $data, $config, $colorsKey, $iconsKey, $config['initialXCoordinate'], $partitionYCoordinate + $config['spaceBetweenRows'], $showLegends, $showSymbols, $showFlags);

        return $currentSlide;
    }

    // /**
    //  * Get ending notes
    //  *
    //  * @param array $writers
    //  * @return string
    //  */
    // function getEndingNotes($writers)
    // {
    //     $result = '';
    //     // Do not show execution time for index
    //     if (!IS_INDEX) {
    //         $result .= date('H:i:s') . " Done writing file(s)" . EOL;
    //         $result .= date('H:i:s') . " Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB" . EOL;
    //     }
    //     // Return
    //     if (CLI) {
    //         $result .= 'The results are stored in the "results" subdirectory.' . EOL;
    //     } else {
    //         if (!IS_INDEX) {
    //             $types = array_values($writers);
    //             $result .= '<p>&nbsp;</p>';
    //             $result .= '<p>Results: ';
    //             foreach ($types as $type) {
    //                 if (!is_null($type)) {
    //                     $resultFile = 'results/' . SCRIPT_FILENAME . '.' . $type;
    //                     if (file_exists($resultFile)) {
    //                         $result .= "<a href='{$resultFile}' class='btn btn-primary'>{$type}</a> ";
    //                     }
    //                 }
    //             }
    //             $result .= '</p>';
    //         }
    //     }
    //     return $result;
    // }
