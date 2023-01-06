<?php

require_once __DIR__ . "/../Services/DataBase.php";

class Currency
{
    const URL = 'https://www.cbr.ru/scripts/XML_daily.asp';

    const VALUTE = 'valute';

    protected DataBase $db;

    public function __construct(public array $valuteList = [])
    {
        $this->db = DataBase::getInstance();
    }

    public function getXml(): ?object
    {
        $content = file_get_contents(self::URL);
        $xml = simplexml_load_string($content);

        if (false === $xml) {
            return null;
        }

        return $xml;
    }

    public function checkValute():void
    {
        $xml = $this->getXml();
        $date = (string)$xml["Date"];

        foreach ($xml->Valute as $value) {
            if (in_array($value->CharCode, $this->valuteList)) {
                $data = $this->getByCharCode($value->CharCode);
                if (!empty($data)) {
                    if (in_array($data[0]["charCode"], $this->valuteList)) {
                        if ($value->Value != $data[0]["value"]) {
                            $params = [
                                ':value' => $value->Value,
                                ':date' => $date,
                                ':charCode' => $value->CharCode,
                            ];
                            $this->updateValute($params);
                            echo "{$value->CharCode} был успешно обновлен!\n";
                        } else {
                            echo "{$value->CharCode} не требует обновления!\n";
                        }
                    }
                } else {
                    $params = [
                        ':id' => $value["ID"],
                        ':numCode' => $value->NumCode,
                        ':charCode' => $value->CharCode,
                        ':nominal' => $value->Nominal,
                        ':name' => $value->Name,
                        ':value' => $value->Value,
                        ':date' => $date,
                    ];
                    $this->insertValute($params);
                    echo "{$value->CharCode} был успешно добавлен!\n";
                }
            }
        }
    }

    public function updateValute($params): array
    {
        $query = "UPDATE `" . self::VALUTE . "` SET value = :value, date = :date WHERE charCode = :charCode";

        return $this->db->query($query, $params);
    }

    public function insertValute($params): array
    {
        $query = "INSERT INTO `" . self::VALUTE . "` (`id`,`numCode`,`charCode`,`nominal`,`name`,`value`,`date`)
        VALUES (:id,:numCode,:charCode,:nominal,:name,:value,:date)";

        return $this->db->query($query, $params);
    }


    public function getByCharCode($charCode): array
    {
        $query = "SELECT * FROM `" . self::VALUTE . "` WHERE charCode = :charCode";
        $params = [':charCode' => $charCode];

        return $this->db->query($query, $params);
    }
}

