<?php

    use App\classes\Db;
    
    /**
     * @param mixed $type is it kids or siblings
     * 
     * @param int $typeCount the number selected
     * @param array $data cleaned POST data
     * @throws \Throwable
     */
    function processKidSibling(string $type, int $typeCount, array $data): void
    {
        try {
            for ($i = 1; $i <= $typeCount; $i++) {
                $dataArr = prepareDataArray($type, $i, $data);

                insertData($type, $dataArr);
            }
        } catch (\Throwable $th) {

            showError($th);
        }
    }

 function prepareDataArray(string $type, int $index, array $data): array
    {
        return [
            "{$type}_name" => $data["{$type}_name$index"],
            "{$type}_email" => $data["{$type}_email$index"],
            "{$type}_linked" => $data["{$type}_option$index"],
            // "{$type}_code" => $data["familyCode"],
            "id" => $data["id"],
        ];
    }

 function insertData(string $type, array $dataArr): void
    {
        $sql = "INSERT INTO {$type}s ({$type}_name, {$type}_email, {$type}_linked, id) 
            VALUES (:{$type}_name, :{$type}_email, :{$type}_linked, :id)";

        $query = Db::connect2()->prepare($sql);
        $query->execute($dataArr);
    }
