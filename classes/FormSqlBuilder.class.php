<?php
class SqlFormCreator {
    public $form_data = []; // $_POST veya başka kaynaklardan gelen veriler
    public $extra_data = []; // Ekstra olarak eklenmek istenen veriler [['sutun_adi', 'değer'], ['sutun_adi', 'değer']]
    public $table_name; // İşlem yapılacak tablonun adı
    public $method = "INSERT"; // "INSERT" veya "UPDATE" kullanılabilir
    public $update_condition = []; // Dinamik olarak belirlenecek WHERE koşul dizisi
    public $input_param = ""; // Hangi input'ların seçileceğini belirler
    public $remove_input = []; // Dahil edilmeyecek input'lar
    public $remove_param = []; // Belirtilen başlangıç karakterini içeren input'ları temizler
    public $column_addition = ""; // Tablo sütunları başına eklenen key

    private $pdo; // PDO bağlantısı

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createSql() {
        if (strtolower($this->method) == "insert") {
            $sql = "INSERT INTO " . $this->table_name . " SET ";
        } elseif (strtolower($this->method) == "update") {
            $sql = "UPDATE " . $this->table_name . " SET ";
        } else {
            $sql = "INSERT INTO " . $this->table_name . " SET ";
        }
    
        $params = [];
        $set_parts = [];
    
        // remove_param içindeki parametreleri form_data dizisinden çıkarıyoruz
        foreach ($this->remove_param as $remove_item) {
            if (isset($this->form_data[$remove_item])) {
                unset($this->form_data[$remove_item]);
            }
        }
    
        // POST verileri işleniyor
        foreach ($this->form_data as $key_raw => $value) {
            // 'file-datatable2_length' gibi parametreler dışarıda bırakılıyor
            if (is_array($value) || in_array($key_raw, ['file-datatable2_length'])) {
                continue;
            }
    
            // Boş değer kontrolü (empty() ile kontrol ediyoruz)
            if ($key_raw != 'statu' && empty($value)) {
                continue; // Boş değeri atla
            }
    
            $key = $this->sanitizeKey($key_raw);
            if ($key !== null) {
                $set_parts[] = "{$this->column_addition}{$key} = :{$key}";
                // 'statu' gibi özel durumlar için farklı değer ataması yapılabilir
                $params[$key] = ($key == "statu" && ($value == "on" || $value == "1")) ? "1" : $value;
            }
        }
    
        // Ekstra veriler ekleniyor
        foreach ($this->extra_data as $extra) {
            if (count($extra) == 2) {
                $key = $extra[0];
                $value = $extra[1];
                // Boş değer kontrolü ekliyoruz
                if (empty($value)) {
                    continue; // Boş değerleri eklemiyoruz
                }
    
                $set_parts[] = "{$this->column_addition}{$key} = :{$key}";
                $params[$key] = $value;
            }
        }
    
        // SET kısmını oluşturuyoruz
        $sql .= implode(", ", $set_parts);
    
        // Eğer UPDATE işlemi yapılacaksa, WHERE koşulunu ekleyelim
        if ($this->method == "UPDATE") {
            // Dinamik WHERE koşulu oluşturuluyor
            if (count($this->update_condition) > 0) {
                $where_parts = [];
                foreach ($this->update_condition as $condition) {
                    if (count($condition) == 2) {
                        $key = $condition[0];
                        $value = $condition[1];
                        if ($key !== '' && !empty($value)) { // Boş anahtar ve değer kontrolü
                            // WHERE koşulundaki parametre adı 'update_' ekleyerek ayrıştırılıyor
                            $where_parts[] = "{$key} = :update_{$key}";
                            $params["update_{$key}"] = $value; // Parametreyi ekleyelim
                        }
                    }
                }
                $sql .= " WHERE " . implode(" AND ", $where_parts);
            }
    
            // Burada id'yi manuel olarak ekliyoruz
            if (isset($this->form_data['id']) && !empty($this->form_data['id'])) {
                $params['id'] = $this->form_data['id'];
            } elseif (isset($this->update_condition['id'])) {
                $params['id'] = $this->update_condition['id'];
            }
        }
    
        return ['query' => $sql, 'params' => $params];
    }
    

    public function execute() {
        $sql_data = $this->createSql();
        $stmt = $this->pdo->prepare($sql_data['query']);
        return $stmt->execute($sql_data['params']);
    }

    private function sanitizeKey($key_raw) {
        if ($this->input_param === "") {
            foreach ($this->remove_param as $remove_item) {
                if (strpos($key_raw, $remove_item) === 0) {
                    return substr($key_raw, strlen($remove_item));
                }
            }
            return $key_raw;
        } elseif (strpos($key_raw, $this->input_param) === 0) {
            return substr($key_raw, strlen($this->input_param));
        }
        return null;
    }
}
?>
