<?php
define('ENDL', "\n");
define('PATH_SLASH', '/');

class IfDefParser
{
    private $listOfFiles;
    private $versionString;
    private $destinationDir;
    private $workDir;

    public function __construct($destinationDir, $workDir = ".", $version = "")
    {
        $this->destinationDir = $destinationDir;

        if (strcmp($version, "") == 0) {
            if (version_compare(PHP_VERSION, '5.2', '>')) {
                define('PHP53', '1');
                $this->versionString = "PHP53";
            } else {
                define('PHP52', '1');
                $this->versionString = "PHP52";
            }
        } else {
            $this->versionString = $version;
            define($version, '1');
        }

        $this->workDir = realpath($workDir);

        if (substr($this->workDir, -1) !== PATH_SLASH) {
            $this->workDir .= PATH_SLASH;
        }

        echo "Will output into " . $this->destinationDir . ENDL;
        echo "Work dir is " . $this->workDir . ENDL;

        $this->listOfFiles = scandir($this->workDir);
        chdir($this->workDir);
    }

    private function _makeFilePath($buf, $outputName)
    {
        if (strstr($buf, "path PHP")) {
            $pathVersion = preg_split('/\s+/', $buf);

            $pathVersion[1] = trim($pathVersion[1]);
            if (defined($pathVersion[1])) {
                $pathVersion[2] = trim($pathVersion[2]);
                $curPath = $this->destinationDir . $pathVersion[2];
                $pathDirs = preg_split('/\//', $curPath);
                $curPath = $pathDirs[0];
                for ($p = 1; $p < count($pathDirs); $p++) {
                    if (is_dir($curPath) !== TRUE && strcmp($curPath, "") != 0) {
                        mkdir($curPath);
                    }
                    $curPath .= '/' . $pathDirs[$p];
                }

                $outputName = $curPath . $outputName;
            }
        }

        chdir($this->workDir);

        return $outputName;
    }

    public function build()
    {
        $filesCount = 0;

        for ($n = 0; $n < count($this->listOfFiles); $n++) {
            if (strpos($this->listOfFiles[$n], ".php_") == FALSE) {
                continue;
            }

            $currentFile = fopen($this->listOfFiles[$n], "r");
            $outputName = trim($this->listOfFiles[$n], '_');

            $buf = fgets($currentFile);
            $buf = fgets($currentFile);

            if (strpos($buf, $this->versionString) && strpos($buf, "none") == FALSE) {
                $outputName = $this->_makeFilePath($buf, $outputName);
            } elseif (strpos($buf, "none") !== FALSE) {
                continue;
            } elseif (strpos($buf, $this->versionString) == FALSE) {
                $buf = fgets($currentFile);

                if (strpos($buf, $this->versionString) && strpos($buf, "none") == FALSE)
                    $outputName = $this->_makeFilePath($buf, $outputName);
                else
                    continue;
            }

            //echo "Current file: ".$this->listOfFiles[$n]."</br>";
            //echo "Will output in ".$outputName."</br>";
            echo $outputName . ENDL;

            $outputFile = fopen($outputName, "w") or die('Cannot open file:  ' . $outputName); //implicitly creates file;

            $contents = file_get_contents($this->listOfFiles[$n]);
            $filesCount++;

            $chunks = preg_split("/#ifdef/", $contents);

            for ($i = 0; $i < count($chunks); $i++) {
                if (strstr($chunks[$i], "#endif") == FALSE) {
                    $outStr = trim($chunks[$i], "#endif");
                    fwrite($outputFile, $outStr);
                    continue;
                }

                $ifdVersion = preg_split('/\s+/', $chunks[$i]);
                $ifdVersion[0] = trim($ifdVersion[1]);
                $chunks2 = preg_split("/#elsif/", $chunks[$i]);
                if (defined($ifdVersion[0])) {
                    $str = str_replace($ifdVersion[0], '', $chunks2[0]);
                    $str = str_replace("#endif", '', $str);
                    fwrite($outputFile, $str);
                } elseif (count($chunks2) == 1 && strstr($chunks2[0], "#endif") !== FALSE) {
                    $chunks3 = preg_split("/#endif/", $chunks2[0]);
                    fwrite($outputFile, $chunks3[1]);
                }

                for ($j = 1; $j < count($chunks2); $j++) {
                    if (defined($ifdVersion[0]) !== TRUE) {
                        $chunks2[$j] = str_replace("#endif", '', $chunks2[$j]);
                        fwrite($outputFile, $chunks2[$j]);
                    } else {
                        $chunks3 = preg_split("/#endif/", $chunks2[$j]);
                        for ($k = 1; $k < count($chunks3); $k++) {
                            fwrite($outputFile, $chunks3[$k]);
                        }
                    }
                }
            }

            fclose($currentFile);
            fclose($outputFile);
        }

        echo "Total: " . $filesCount . " files were processed." . ENDL;
    }
}
