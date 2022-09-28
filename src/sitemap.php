<?php

class siteMap
{
    protected array $attribute;
    protected string $type;
    protected string $output;

    public function __construct($attr, $fileType, $outputFile)
    {
        if (!empty($attr) || !empty($fileType) || !empty($outputFile)) {
            try {
                $this->attribute = $attr;
                $this->type = $fileType;
                $this->output = $outputFile;
            } catch (Exception $e) {
                echo 'Поймано исключение: ',  $e->getMessage(), "\n";
            }
        } else {
            $e = new ErrorException('Аргумент/ы не имеют значения');
            echo 'Поймано исключение: ',  $e->getMessage(), "\n";
        }


        switch ($fileType) {
        case 'xml':
            $this->createXML();
            break;
        case 'json':
            $this->createJSON();
            break;
        case 'csv':
            $this->createCSV();
            break;
        default:
            $e = new InvalidArgumentException('Не вверно введён аргумент fileType, попробуйте: csv, json, xml');
            echo 'Поймано исключение: ',  $e->getMessage(), "\n";
            break;
        }
    }

    private function createXML() {
        try {
            $dom = new DOMDocument('1.0', 'utf-8');
            $urlset = $dom->createElement('urlset');
            $urlset->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/siteMap/0.9');
            $urlset->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/siteMap/0.9 
            http://www.sitemaps.org/schemas/siteMap/0.9/siteMap.xsd');

            foreach ($this->attribute as $items) {
                // Элемент <loc> - URL статьи
                $url = $dom->createElement('url');
                $loc = $dom->createElement('loc');
                $text = $dom->createTextNode(
                    htmlentities($items['loc'], ENT_QUOTES)
                );
                $loc->appendChild($text);
                $url->appendChild($loc);

                // Элемент <lastmod> - дата последнего изменения статьи
                $lastmod = $dom->createElement('lastmod');
                $text = $dom->createTextNode($items['lastmod']);
                $lastmod->appendChild($text);
                $url->appendChild($lastmod);

                // Элемент <priority> - приоритетность
                $priority = $dom->createElement('priority');
                $text = $dom->createTextNode($items['priority']);
                $priority->appendChild($text);
                $url->appendChild($priority);

                $urlset->appendChild($url);
            }
            // сохранение в файле
            $dom->appendChild($urlset);

            // создаём папку, если не существует
            if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $this->output)) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . $this->output, 0777, true);
                $dom->save($_SERVER['DOCUMENT_ROOT'] . $this->output . '/sitemap.xml');
                // отправка в браузер.
                header('Content-Type: text/xml');
                echo $dom->saveXML();
                exit();
            } else {
                $dom->save($_SERVER['DOCUMENT_ROOT'] . $this->output . '/sitemap.xml');
                // отправка в браузер.
                header('Content-Type: text/xml');
                echo $dom->saveXML();
                exit();
            }
        } catch (Exception $e) {
            echo 'Поймано исключение: ',  $e->getMessage(), "\n";
        }

    }

    private function createJSON() {
        try {
            $json = json_encode($this->attribute);
            // создаём папку, если не существует
            if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $this->output)) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . $this->output, 0777, true);
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . $this->output . '/sitemap.json', $json);
            } else {
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . $this->output . '/sitemap.json', $json);
            }
        } catch (Exception $e) {
            echo 'Поймано исключение: ',  $e->getMessage(), "\n";
        }

    }

    private function createCSV() {
        try {
            $csvArr = array();
            $titles = array();
            $body = array();
            // создаём папку, если не существует
            if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $this->output)) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . $this->output, 0777, true);

                foreach ($this->attribute as $items) {
                    foreach ($items as $key => $value) {
                        array_push($titles, $key);
                    }
                }
                print_r($titles);
            } else {
                foreach ($this->attribute as $items) {
                    foreach ($items as $key => $value) {
                        array_push($titles, $key);
                    }
                    break;
                }
                foreach ($this->attribute as $items) {
                    $tempArr = array();
                    foreach ($items as $key => $value) {
                        array_push($tempArr, $value);
                    }
                    array_push($body, $tempArr);
                }
                array_push($csvArr, $titles);

                foreach ($body as $items) {
                    array_push($csvArr, $items);
                }

                file_put_contents($_SERVER['DOCUMENT_ROOT'] . $this->output . '/sitemap.csv', '');
                $fp = fopen($_SERVER['DOCUMENT_ROOT'] . $this->output . '/sitemap.csv', 'w+');
                foreach ($csvArr as $fields) {
                    fputcsv($fp, $fields, ';');
                }
            }
        } catch (Exception $e) {
            echo 'Поймано исключение: ',  $e->getMessage(), "\n";
        }
    }
}