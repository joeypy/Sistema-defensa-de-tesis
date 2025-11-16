<?php

class ApiProveedor {
    private $apiKey;
    private $endpoint;

    public function __construct($apiKey, $endpoint) {
        $this->apiKey = $apiKey;
        $this->endpoint = $endpoint;
    }

    public function obtenerProductos() {
        // Ejemplo de implementación para un proveedor específico
        $url = $this->endpoint . '/productos?api_key=' . $this->apiKey;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        
        if ($response === false) {
            throw new Exception('Error en la petición cURL: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if ($data === null) {
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }
        
        if (!isset($data['resultados']) || !is_array($data['resultados'])) {
            throw new Exception('Estructura de respuesta inválida: no se encontraron resultados');
        }
        
        // Normalizar datos para nuestro sistema
        $productos = [];
        foreach ($data['resultados'] as $item) {
            if (!is_array($item)) {
                continue; // Saltar si no es un array
            }
            
            $productos[] = [
                'codigo' => $item['sku'] ?? '',
                'nombre' => $item['nombre'] ?? '',
                'precio' => $item['precio_compra'] ?? 0,
                'color' => $item['color_principal'] ?? '',
                'stock' => isset($item['disponible']) ? ($item['disponible'] ? 1 : 0) : 0
            ];
        }
        
        return $productos;
    }

    public function realizarPedido($productos) {
        $url = $this->endpoint . '/pedidos';
        $payload = [
            'api_key' => $this->apiKey,
            'items' => $productos
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}