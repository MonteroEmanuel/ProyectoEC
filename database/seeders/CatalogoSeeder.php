<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Database\Seeder;

// Datos de prueba de la tienda de componentes de PC.
// Solo se crean los productos que tienen una imagen oficial (URL del fabricante) verificada:
// una imagen por familia (ej. la insignia Core i7 sirve para todos los i7). Ver IMAGENES_OFICIALES.md.
class CatalogoSeeder extends Seeder
{
    private const CORSAIR = 'https://res.cloudinary.com/corsair-pwa/image/upload/c_scale%2Cq_auto%2Cw_500/products/';
    private const INTEL = 'https://www.intel.com/content/dam/www/central-libraries/xl/es/images/2022-06/';

    // Familia => URL oficial de la imagen
    private const IMAGENES = [
        'amd-ryzen-5'  => 'https://www.amd.com/content/dam/amd/en/images/products/processors/ryzen/2613900-ryzen-5-9600x-og.jpg',
        'amd-ryzen-7'  => 'https://www.amd.com/content/dam/amd/en/images/products/processors/ryzen/2505503-ryzen-7-7800x3d-og.jpg',
        'amd-ryzen-9'  => 'https://www.amd.com/content/dam/amd/en/images/products/processors/ryzen/2613900-ryzen-9-9950x-og.jpg',
        'intel-i3'     => self::INTEL . 'km-1920x1080-i3.png',
        'intel-i5'     => self::INTEL . 'km-1920x1080-i5.png',
        'intel-i7'     => self::INTEL . 'km-1920x1080-i7.png',
        'intel-i9'     => 'https://www.intel.com/content/dam/www/central-libraries/xa/en/images/intel-core-i9-badge-1920x1080.png',
        'intel-arc'    => 'https://intelcorp.scene7.com/is/image/intelcorp/badge-arc-graphics-straight:1080-1080?fmt=png8-alpha',
        'placa-asus'   => 'https://dlcdnwebimgs.asus.com/gain/d52e1dd0-6b0b-4c62-a625-01c5e41a8b07/',
        'placa-msi'    => 'https://storage-asset.msi.com/global/picture/product/product_166478620721bbeb5fac5e7800fc1614cf6e023720.webp',
        'placa-gigabyte' => 'https://static.gigabyte.com/StaticFile/Image/Global/db171cf121e076c5ac63c879c3820676/ProductRemoveBg/32102',
        'gpu-asus'     => 'https://dlcdnwebimgs.asus.com/gain/61d4f727-4456-41bf-b4a8-51bcf0c9eea5/',
        'gpu-msi'      => 'https://storage-asset.msi.com/global/picture/product/product_1704703822984da48f4e57873dd8ba9f899f1df6ac.webp',
        'gpu-gigabyte' => 'https://static.gigabyte.com/StaticFile/Image/Global/2977ff92cb7a0ad8f654e25772db9f25/ProductRemoveBg/34882',
        'ram-corsair'  => self::CORSAIR . 'Memory/vengeance-ddr5-blk-config/Gallery/Vengeance-DDR5-2UP-16GB-BLACK_01.png',
        'psu-corsair'  => self::CORSAIR . 'Power-Supply-Units/CP-9020180-NA/Gallery/RM850x_PSU_01.png',
        'gabinete-corsair-4000d' => self::CORSAIR . 'Cases/base-4000d-airflow-config/Gallery/4000D_AF_BLACK_01.png',
        'gabinete-nzxt-h' => 'https://nzxt.com/media/c7bd8d8298e928db6d8162b3b38609d459cce6879a261f6e6dbf38fe50b0f02c',
        'aio-corsair'  => self::CORSAIR . 'Custom-Cooling/CW-9060075-WW/-base-elite-lcd-xt-cooler-config-Gallery-H150i-ELITE-LCD-XT-01.webp',
        'teclado-corsair-k70' => self::CORSAIR . 'Gaming-Keyboards/CH-9109410-NA/Gallery/K70_RGB_PRO_PBT_01.png',
        'mouse-corsair-m65'   => self::CORSAIR . 'Gaming-Mice/base-m65-rgb-ultra-config/Gallery/M65_RGB_ULTRA_01.png',
        'audifonos-corsair-hs80' => self::CORSAIR . 'Gaming-Headsets/CA-9011235-NA/Gallery/HS80_RGB_WIRELESS_BLACK_01.png',
        'audifonos-hyperx-cloud' => 'https://hyperx.com/cdn/shop/files/hyperx_cloud_iii_red_66x0049_main_1_c34cc86e-20fb-464e-99c2-c898f103089b.jpg',
    ];

    // Categoría hija => categoría padre. Las categorías y marcas se crean solo si un producto las usa.
    private const PADRES = [
        'Procesadores AMD' => 'Procesadores', 'Procesadores Intel' => 'Procesadores',
        'Placas AM5' => 'Placas madre', 'Placas LGA1700' => 'Placas madre',
        'GPU NVIDIA' => 'Tarjetas de video', 'GPU AMD' => 'Tarjetas de video', 'GPU Intel' => 'Tarjetas de video',
        'RAM DDR4' => 'Memorias RAM', 'RAM DDR5' => 'Memorias RAM',
        'SSD NVMe' => 'Almacenamiento', 'SSD SATA' => 'Almacenamiento', 'Discos duros' => 'Almacenamiento',
        'Refrigeración líquida' => 'Refrigeración', 'Disipadores por aire' => 'Refrigeración',
        'Teclados' => 'Periféricos', 'Mouse' => 'Periféricos', 'Audífonos' => 'Periféricos',
        'Webcams' => 'Periféricos', 'Micrófonos' => 'Periféricos', 'Mousepads' => 'Periféricos',
    ];

    private array $cat = [];
    private array $marca = [];

    public function run(): void
    {
        // Semilla fija: stock y estados iguales en cada ejecución
        mt_srand(2026);

        $this->procesadores();
        $this->placasMadre();
        $this->tarjetasDeVideo();
        $this->memoriasRam();
        $this->almacenamiento();
        $this->fuentesDePoder();
        $this->gabinetes();
        $this->refrigeracion();
        $this->monitores();
        $this->perifericos();
    }

    // ---------------------------------------------------------------
    // Imágenes oficiales
    // ---------------------------------------------------------------

    // Devuelve la URL oficial que corresponde al producto, o null si no hay (el producto no se crea)
    private function imagenOficial(string $nombre, string $categoria, string $marca): ?string
    {
        $familia = match (true) {
            $categoria === 'Procesadores AMD' && str_contains($nombre, 'Ryzen 5') => 'amd-ryzen-5',
            $categoria === 'Procesadores AMD' && str_contains($nombre, 'Ryzen 7') => 'amd-ryzen-7',
            $categoria === 'Procesadores AMD' && str_contains($nombre, 'Ryzen 9') => 'amd-ryzen-9',
            $categoria === 'Procesadores Intel' && str_contains($nombre, 'Core i3') => 'intel-i3',
            $categoria === 'Procesadores Intel' && str_contains($nombre, 'Core i5') => 'intel-i5',
            $categoria === 'Procesadores Intel' && str_contains($nombre, 'Core i7') => 'intel-i7',
            $categoria === 'Procesadores Intel' && str_contains($nombre, 'Core i9') => 'intel-i9',
            in_array($categoria, ['Placas AM5', 'Placas LGA1700']) && $marca === 'ASUS' => 'placa-asus',
            in_array($categoria, ['Placas AM5', 'Placas LGA1700']) && $marca === 'MSI' => 'placa-msi',
            in_array($categoria, ['Placas AM5', 'Placas LGA1700']) && $marca === 'Gigabyte' => 'placa-gigabyte',
            $categoria === 'GPU NVIDIA' && $marca === 'ASUS' => 'gpu-asus',
            $categoria === 'GPU NVIDIA' && $marca === 'MSI' => 'gpu-msi',
            $categoria === 'GPU NVIDIA' && $marca === 'Gigabyte' => 'gpu-gigabyte',
            $categoria === 'GPU Intel' && $marca === 'Intel' => 'intel-arc',
            $categoria === 'RAM DDR5' && $marca === 'Corsair' => 'ram-corsair',
            $categoria === 'Fuentes de poder' && $marca === 'Corsair' => 'psu-corsair',
            $categoria === 'Gabinetes' && str_contains($nombre, 'Corsair 4000D') => 'gabinete-corsair-4000d',
            $categoria === 'Gabinetes' && $marca === 'NZXT' => 'gabinete-nzxt-h',
            $categoria === 'Refrigeración líquida' && $marca === 'Corsair' => 'aio-corsair',
            $categoria === 'Teclados' && str_contains($nombre, 'Corsair K70') => 'teclado-corsair-k70',
            $categoria === 'Mouse' && str_contains($nombre, 'Corsair M65') => 'mouse-corsair-m65',
            $categoria === 'Audífonos' && str_contains($nombre, 'Corsair HS80') => 'audifonos-corsair-hs80',
            $categoria === 'Audífonos' && $marca === 'HyperX' => 'audifonos-hyperx-cloud',
            default => null,
        };

        return $familia ? self::IMAGENES[$familia] : null;
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function categoria(string $nombre): Categoria
    {
        if (!isset($this->cat[$nombre])) {
            $padre = self::PADRES[$nombre] ?? null;
            $padreId = $padre ? $this->categoria($padre)->id : null;
            $this->cat[$nombre] = Categoria::firstOrCreate(['nombre' => $nombre], ['padre_id' => $padreId]);
        }
        return $this->cat[$nombre];
    }

    private function marca(string $nombre): Marca
    {
        return $this->marca[$nombre] ??= Marca::firstOrCreate(['nombre' => $nombre]);
    }

    // Redondea a precios tipo $189.990
    private function precio(float $valor): int
    {
        return (int) (round($valor / 1000) * 1000 - 10);
    }

    private function crear(string $nombre, string $categoria, string $marca, float $precio, string $descripcion): void
    {
        // ~10% sin stock, ~15% con pocas unidades, resto con stock normal
        $r = mt_rand(1, 100);
        $stock = $r <= 10 ? 0 : ($r <= 25 ? mt_rand(1, 5) : mt_rand(6, 60));

        // ~4% inactivos (no deben aparecer en la tienda)
        $estado = mt_rand(1, 100) <= 4 ? 'inactivo' : 'activo';

        // Sin imagen oficial, el producto no se crea
        $imagen = $this->imagenOficial($nombre, $categoria, $marca);
        if (!$imagen) {
            return;
        }

        $producto = Producto::firstOrCreate(['nombre' => $nombre], [
            'categoria_id' => $this->categoria($categoria)->id,
            'marca_id'     => $this->marca($marca)->id,
            'descripcion'  => $descripcion,
            'precio'       => $this->precio($precio),
            'stock'        => $stock,
            'url_imagen'   => $imagen,
            'estado'       => $estado,
        ]);

        // Productos creados por una versión anterior del seeder (sin imagen)
        if (!$producto->url_imagen) {
            $producto->update(['url_imagen' => $imagen]);
        }
    }

    // ---------------------------------------------------------------
    // Productos por categoría
    // ---------------------------------------------------------------

    private function procesadores(): void
    {
        // [modelo, núcleos, hilos, GHz máx, socket, precio]
        $amd = [
            ['Ryzen 5 5600', 6, 12, '4,4', 'AM4', 99990],
            ['Ryzen 7 5700X3D', 8, 16, '4,1', 'AM4', 229990],
            ['Ryzen 5 7600', 6, 12, '5,1', 'AM5', 189990],
            ['Ryzen 5 9600X', 6, 12, '5,4', 'AM5', 259990],
            ['Ryzen 7 7700X', 8, 16, '5,4', 'AM5', 289990],
            ['Ryzen 7 7800X3D', 8, 16, '5,0', 'AM5', 389990],
            ['Ryzen 7 9800X3D', 8, 16, '5,2', 'AM5', 519990],
            ['Ryzen 9 7900X', 12, 24, '5,6', 'AM5', 409990],
            ['Ryzen 9 9950X', 16, 32, '5,7', 'AM5', 649990],
        ];
        foreach ($amd as [$modelo, $nucleos, $hilos, $ghz, $socket, $precio]) {
            $this->crear("Procesador AMD $modelo", 'Procesadores AMD', 'AMD', $precio,
                "Procesador de $nucleos núcleos y $hilos hilos, frecuencia de hasta $ghz GHz y socket $socket.");
        }

        $intel = [
            ['Core i3-14100F', 4, 8, '4,7', 109990],
            ['Core i5-12400F', 6, 12, '4,4', 119990],
            ['Core i5-14400F', 10, 16, '4,7', 199990],
            ['Core i5-14600K', 14, 20, '5,3', 299990],
            ['Core i7-14700K', 20, 28, '5,6', 429990],
            ['Core i9-14900K', 24, 32, '6,0', 589990],
            ['Core i9-14900KS', 24, 32, '6,2', 689990],
        ];
        foreach ($intel as [$modelo, $nucleos, $hilos, $ghz, $precio]) {
            $this->crear("Procesador Intel $modelo", 'Procesadores Intel', 'Intel', $precio,
                "Procesador de $nucleos núcleos y $hilos hilos, frecuencia de hasta $ghz GHz y socket LGA1700.");
        }
    }

    private function placasMadre(): void
    {
        // [chipset, subcategoría, formato, extra, precio base]
        $chipsets = [
            ['A620', 'Placas AM5', 'Micro-ATX', '', 99990],
            ['B650', 'Placas AM5', 'ATX', 'WiFi 6E', 179990],
            ['B650E', 'Placas AM5', 'ATX', 'WiFi 6E', 229990],
            ['X670E', 'Placas AM5', 'ATX', 'WiFi 7', 389990],
            ['H610', 'Placas LGA1700', 'Micro-ATX', '', 89990],
            ['B760', 'Placas LGA1700', 'ATX', 'WiFi 6', 159990],
            ['Z790', 'Placas LGA1700', 'ATX', 'WiFi 7', 299990],
        ];
        $marcas = ['ASUS' => 1.05, 'MSI' => 1.0, 'Gigabyte' => 0.97, 'ASRock' => 0.92];

        foreach ($chipsets as [$chipset, $sub, $formato, $extra, $base]) {
            foreach ($marcas as $marca => $factor) {
                $socket = $sub === 'Placas AM5' ? 'AM5' : 'LGA1700';
                $nombre = trim("Placa Madre $marca $chipset $formato $extra");
                $this->crear($nombre, $sub, $marca, $base * $factor,
                    "Placa madre socket $socket, chipset $chipset, formato $formato" . ($extra ? " y conectividad $extra" : '') . '. Soporte para memorias DDR5 y ranuras M.2 NVMe.');
            }
        }
    }

    private function tarjetasDeVideo(): void
    {
        // [modelo, memoria, subcategoría, precio base, fabricantes]
        $modelos = [
            ['GeForce RTX 4060', '8 GB GDDR6', 'GPU NVIDIA', 329990, ['ASUS', 'MSI', 'Gigabyte', 'Zotac']],
            ['GeForce RTX 4060 Ti', '8 GB GDDR6', 'GPU NVIDIA', 419990, ['ASUS', 'MSI', 'Zotac']],
            ['GeForce RTX 4070', '12 GB GDDR6X', 'GPU NVIDIA', 559990, ['MSI', 'Gigabyte', 'Zotac']],
            ['GeForce RTX 4070 Super', '12 GB GDDR6X', 'GPU NVIDIA', 649990, ['ASUS', 'MSI', 'Gigabyte']],
            ['GeForce RTX 4070 Ti Super', '16 GB GDDR6X', 'GPU NVIDIA', 849990, ['ASUS', 'Gigabyte']],
            ['GeForce RTX 4080 Super', '16 GB GDDR6X', 'GPU NVIDIA', 1149990, ['ASUS', 'MSI', 'Zotac']],
            ['GeForce RTX 4090', '24 GB GDDR6X', 'GPU NVIDIA', 1999990, ['ASUS', 'MSI']],
            ['Radeon RX 7600', '8 GB GDDR6', 'GPU AMD', 279990, ['Sapphire', 'PowerColor', 'ASRock']],
            ['Radeon RX 7700 XT', '12 GB GDDR6', 'GPU AMD', 449990, ['Sapphire', 'PowerColor']],
            ['Radeon RX 7800 XT', '16 GB GDDR6', 'GPU AMD', 559990, ['Sapphire', 'PowerColor', 'Gigabyte']],
            ['Radeon RX 7900 XT', '20 GB GDDR6', 'GPU AMD', 789990, ['Sapphire', 'ASRock']],
            ['Radeon RX 7900 XTX', '24 GB GDDR6', 'GPU AMD', 999990, ['Sapphire', 'PowerColor', 'ASUS']],
            ['Arc A580', '8 GB GDDR6', 'GPU Intel', 189990, ['ASRock']],
            ['Arc A770', '16 GB GDDR6', 'GPU Intel', 299990, ['ASRock', 'Intel']],
        ];
        $series = [
            'ASUS' => ['TUF Gaming OC', 1.06], 'MSI' => ['Gaming X', 1.04], 'Gigabyte' => ['Eagle OC', 1.0],
            'Zotac' => ['Twin Edge', 0.97], 'Sapphire' => ['Pulse', 1.0], 'PowerColor' => ['Hellhound', 1.02],
            'ASRock' => ['Challenger', 0.96], 'Intel' => ['Limited Edition', 1.0],
        ];

        foreach ($modelos as [$modelo, $memoria, $sub, $base, $fabricantes]) {
            foreach ($fabricantes as $marca) {
                [$serie, $factor] = $series[$marca];
                $this->crear("Tarjeta de Video $marca $modelo $serie $memoria", $sub, $marca, $base * $factor,
                    "Tarjeta gráfica $modelo con $memoria, edición $serie de $marca con sistema de refrigeración de alto rendimiento.");
            }
        }
    }

    private function memoriasRam(): void
    {
        $marcas = ['Kingston' => 'Fury Beast', 'Corsair' => 'Vengeance', 'G.Skill' => 'Ripjaws', 'Crucial' => 'Pro', 'TeamGroup' => 'T-Force Delta'];

        // DDR4: [capacidad total, módulos, velocidad, precio]
        $ddr4 = [['16 GB', '2x8', 3200, 44990], ['32 GB', '2x16', 3200, 79990], ['32 GB', '2x16', 3600, 89990]];
        // DDR5: [capacidad total, módulos, velocidad, precio]
        $ddr5 = [['32 GB', '2x16', 5600, 109990], ['32 GB', '2x16', 6000, 129990], ['64 GB', '2x32', 6000, 239990]];

        foreach ($marcas as $marca => $serie) {
            foreach ($ddr4 as [$cap, $mod, $mhz, $precio]) {
                $this->crear("Memoria RAM $marca $serie DDR4 $cap ($mod) $mhz MHz", 'RAM DDR4', $marca, $precio * (mt_rand(95, 108) / 100),
                    "Kit de memoria DDR4 de $cap en $mod módulos a $mhz MHz con perfil XMP y disipador de aluminio.");
            }
            foreach ($ddr5 as [$cap, $mod, $mhz, $precio]) {
                $this->crear("Memoria RAM $marca $serie DDR5 $cap ($mod) $mhz MHz", 'RAM DDR5', $marca, $precio * (mt_rand(95, 108) / 100),
                    "Kit de memoria DDR5 de $cap en $mod módulos a $mhz MHz con perfiles XMP/EXPO.");
            }
        }
    }

    private function almacenamiento(): void
    {
        // SSD NVMe: [marca, serie, velocidad lectura]
        $nvme = [['Samsung', '990 Pro', 7450], ['Western Digital', 'Black SN850X', 7300], ['Kingston', 'KC3000', 7000], ['Crucial', 'T500', 7400]];
        $capNvme = ['500 GB' => 59990, '1 TB' => 89990, '2 TB' => 159990, '4 TB' => 329990];
        foreach ($nvme as [$marca, $serie, $lectura]) {
            foreach ($capNvme as $cap => $precio) {
                $this->crear("SSD NVMe $marca $serie $cap PCIe 4.0", 'SSD NVMe', $marca, $precio * (mt_rand(92, 110) / 100),
                    "Unidad M.2 NVMe PCIe 4.0 de $cap con velocidades de lectura de hasta " . number_format($lectura, 0, ',', '.') . ' MB/s.');
            }
        }

        // SSD SATA
        $sata = [['Kingston', 'A400'], ['Crucial', 'MX500'], ['Samsung', '870 EVO']];
        $capSata = ['480 GB' => 34990, '1 TB' => 59990, '2 TB' => 109990];
        foreach ($sata as [$marca, $serie]) {
            foreach ($capSata as $cap => $precio) {
                $this->crear("SSD SATA $marca $serie $cap 2.5\"", 'SSD SATA', $marca, $precio * (mt_rand(92, 110) / 100),
                    "Unidad de estado sólido SATA III de 2,5\" y $cap, ideal para revivir equipos antiguos.");
            }
        }

        // Discos duros
        $hdd = [['Seagate', 'BarraCuda'], ['Western Digital', 'Blue']];
        $capHdd = ['1 TB' => 49990, '2 TB' => 64990, '4 TB' => 99990, '8 TB' => 189990];
        foreach ($hdd as [$marca, $serie]) {
            foreach ($capHdd as $cap => $precio) {
                $this->crear("Disco Duro $marca $serie $cap 3.5\"", 'Discos duros', $marca, $precio,
                    "Disco duro de 3,5\" y $cap a 7200 RPM para almacenamiento masivo de juegos, fotos y respaldos.");
            }
        }
    }

    private function fuentesDePoder(): void
    {
        $marcas = [
            'Corsair' => ['RM', 'Gold'], 'Seasonic' => ['Focus GX', 'Gold'],
            'Cooler Master' => ['MWE', 'Bronze'], 'MSI' => ['MAG A', 'Bronze'],
        ];
        $watts = [550 => 69990, 650 => 84990, 750 => 99990, 850 => 124990, 1000 => 169990];

        foreach ($marcas as $marca => [$serie, $cert]) {
            foreach ($watts as $w => $precio) {
                $factor = $cert === 'Gold' ? 1.15 : 0.9;
                $modular = $cert === 'Gold' ? 'full modular' : 'no modular';
                $this->crear("Fuente de Poder $marca $serie {$w}W 80 Plus $cert", 'Fuentes de poder', $marca, $precio * $factor,
                    "Fuente de poder de {$w}W con certificación 80 Plus $cert, cableado $modular y protecciones OVP/OCP/SCP.");
            }
        }
    }

    private function gabinetes(): void
    {
        $gabinetes = [
            ['NZXT', 'H5 Flow', 'Mid Tower', 99990],
            ['NZXT', 'H7 Flow', 'Mid Tower', 149990],
            ['Lian Li', 'O11 Dynamic EVO', 'Mid Tower', 189990],
            ['Lian Li', 'Lancool 216', 'Mid Tower', 109990],
            ['Fractal Design', 'North', 'Mid Tower', 169990],
            ['Fractal Design', 'Pop Air', 'Mid Tower', 89990],
            ['Corsair', '4000D Airflow', 'Mid Tower', 109990],
            ['Corsair', '5000X RGB', 'Mid Tower', 199990],
            ['Cooler Master', 'MasterBox Q300L', 'Micro-ATX', 59990],
            ['Cooler Master', 'HAF 700', 'Full Tower', 299990],
            ['DeepCool', 'CH560', 'Mid Tower', 89990],
            ['DeepCool', 'CC560', 'Mid Tower', 54990],
        ];
        foreach ($gabinetes as [$marca, $modelo, $formato, $precio]) {
            $this->crear("Gabinete $marca $modelo $formato", 'Gabinetes', $marca, $precio,
                "Gabinete $formato con panel de vidrio templado, excelente flujo de aire y espacio para tarjetas de video largas.");
        }
    }

    private function refrigeracion(): void
    {
        $liquida = ['Corsair' => 'iCUE H', 'NZXT' => 'Kraken', 'Arctic' => 'Liquid Freezer III', 'DeepCool' => 'LT'];
        $radiadores = [240 => 89990, 280 => 109990, 360 => 139990];
        foreach ($liquida as $marca => $serie) {
            foreach ($radiadores as $mm => $precio) {
                $this->crear("Refrigeración Líquida $marca $serie {$mm}mm", 'Refrigeración líquida', $marca, $precio * (mt_rand(90, 115) / 100),
                    "Sistema de refrigeración líquida AIO con radiador de {$mm} mm, compatible con sockets AM5 y LGA1700.");
            }
        }

        $aire = [
            ['Noctua', 'NH-D15', 129990], ['Noctua', 'NH-U12S', 89990],
            ['DeepCool', 'AK620', 64990], ['DeepCool', 'AK400', 34990],
            ['Cooler Master', 'Hyper 212 Black', 39990], ['Arctic', 'Freezer 36', 39990],
            ['Corsair', 'A115', 99990], ['Arctic', 'Freezer 7X', 19990],
        ];
        foreach ($aire as [$marca, $modelo, $precio]) {
            $this->crear("Disipador por Aire $marca $modelo", 'Disipadores por aire', $marca, $precio,
                "Disipador de CPU por aire con heatpipes de cobre y ventilador PWM silencioso.");
        }
    }

    private function monitores(): void
    {
        $monitores = [
            ['AOC', '24G2SP', '24"', 'Full HD', 'IPS', 165, 149990],
            ['AOC', 'Q27G3XMN', '27"', 'QHD', 'Mini LED', 180, 299990],
            ['LG', 'UltraGear 24GS60F', '24"', 'Full HD', 'IPS', 180, 159990],
            ['LG', 'UltraGear 27GR75Q', '27"', 'QHD', 'IPS', 165, 279990],
            ['LG', 'UltraGear 32GS95UE', '32"', '4K', 'OLED', 240, 1299990],
            ['Samsung', 'Odyssey G5 27"', '27"', 'QHD', 'VA curvo', 165, 249990],
            ['Samsung', 'Odyssey G7 32"', '32"', '4K', 'IPS', 144, 599990],
            ['Samsung', 'Odyssey OLED G8', '34"', 'UWQHD', 'OLED', 175, 999990],
            ['ASUS', 'TUF Gaming VG249Q3A', '24"', 'Full HD', 'IPS', 180, 169990],
            ['ASUS', 'ROG Strix XG27ACS', '27"', 'QHD', 'IPS', 180, 349990],
            ['ASUS', 'ROG Swift PG27AQDM', '27"', 'QHD', 'OLED', 240, 899990],
            ['MSI', 'G244F E2', '24"', 'Full HD', 'IPS', 180, 129990],
            ['MSI', 'MAG 274QRF QD E2', '27"', 'QHD', 'Rapid IPS', 180, 329990],
            ['Gigabyte', 'G27Q', '27"', 'QHD', 'IPS', 170, 259990],
            ['Gigabyte', 'M27U', '27"', '4K', 'IPS', 160, 499990],
        ];
        foreach ($monitores as [$marca, $modelo, $pulgadas, $res, $panel, $hz, $precio]) {
            $this->crear("Monitor $marca $modelo $res {$hz}Hz", 'Monitores', $marca, $precio,
                "Monitor de $pulgadas con panel $panel, resolución $res, $hz Hz y tecnología de sincronización adaptativa.");
        }
    }

    private function perifericos(): void
    {
        // [nombre, subcategoría, marca, precio, descripción]
        $perifericos = [
            // Teclados
            ['Teclado Mecánico Logitech G Pro X TKL', 'Teclados', 'Logitech', 149990, 'Teclado mecánico tenkeyless inalámbrico LIGHTSPEED con switches GX.'],
            ['Teclado Logitech G213 Prodigy', 'Teclados', 'Logitech', 49990, 'Teclado de membrana resistente a salpicaduras con iluminación RGB.'],
            ['Teclado Mecánico Razer BlackWidow V4', 'Teclados', 'Razer', 169990, 'Teclado mecánico full size con switches Razer Green y reposamuñecas.'],
            ['Teclado Razer Huntsman Mini 60%', 'Teclados', 'Razer', 109990, 'Teclado compacto 60% con switches ópticos lineales.'],
            ['Teclado Mecánico HyperX Alloy Origins Core', 'Teclados', 'HyperX', 89990, 'Teclado TKL con cuerpo de aluminio y switches HyperX Red.'],
            ['Teclado Mecánico Redragon Kumara K552', 'Teclados', 'Redragon', 34990, 'Teclado mecánico TKL económico con switches Outemu Blue.'],
            ['Teclado Mecánico Corsair K70 RGB Pro', 'Teclados', 'Corsair', 159990, 'Teclado mecánico full size con switches Cherry MX y marco de aluminio.'],
            ['Teclado SteelSeries Apex 3 TKL', 'Teclados', 'SteelSeries', 59990, 'Teclado silencioso resistente al agua con iluminación RGB por zonas.'],
            // Mouse
            ['Mouse Logitech G Pro X Superlight 2', 'Mouse', 'Logitech', 149990, 'Mouse inalámbrico de 60 g con sensor HERO 2 de 32.000 DPI.'],
            ['Mouse Logitech G203 Lightsync', 'Mouse', 'Logitech', 24990, 'Mouse con cable, sensor de 8.000 DPI e iluminación RGB.'],
            ['Mouse Razer DeathAdder V3 Pro', 'Mouse', 'Razer', 139990, 'Mouse inalámbrico ergonómico de 63 g con sensor Focus Pro 30K.'],
            ['Mouse Razer Basilisk V3', 'Mouse', 'Razer', 59990, 'Mouse ergonómico con rueda HyperScroll y 11 botones programables.'],
            ['Mouse HyperX Pulsefire Haste 2', 'Mouse', 'HyperX', 49990, 'Mouse ultraliviano de 53 g con sensor de 26.000 DPI.'],
            ['Mouse Redragon Cobra M711', 'Mouse', 'Redragon', 19990, 'Mouse gamer económico de 10.000 DPI con iluminación RGB.'],
            ['Mouse SteelSeries Rival 3', 'Mouse', 'SteelSeries', 29990, 'Mouse con sensor TrueMove Core y switches de 60 millones de clics.'],
            ['Mouse Corsair M65 RGB Ultra', 'Mouse', 'Corsair', 79990, 'Mouse FPS con estructura de aluminio y pesos ajustables.'],
            // Audífonos
            ['Audífonos HyperX Cloud III', 'Audífonos', 'HyperX', 89990, 'Headset con drivers de 53 mm, micrófono desmontable y gran comodidad.'],
            ['Audífonos HyperX Cloud Alpha Wireless', 'Audífonos', 'HyperX', 169990, 'Headset inalámbrico con hasta 300 horas de batería.'],
            ['Audífonos Logitech G Pro X 2', 'Audífonos', 'Logitech', 219990, 'Headset inalámbrico con drivers de grafeno y micrófono profesional.'],
            ['Audífonos Razer BlackShark V2 X', 'Audífonos', 'Razer', 49990, 'Headset liviano con sonido envolvente 7.1 y micrófono cardioide.'],
            ['Audífonos SteelSeries Arctis Nova 7', 'Audífonos', 'SteelSeries', 179990, 'Headset inalámbrico multiplataforma con conexión simultánea Bluetooth.'],
            ['Audífonos Corsair HS80 RGB Wireless', 'Audífonos', 'Corsair', 139990, 'Headset inalámbrico con audio espacial Dolby Atmos.'],
            ['Audífonos Redragon Zeus H510', 'Audífonos', 'Redragon', 39990, 'Headset con sonido 7.1 virtual y almohadillas de espuma viscoelástica.'],
            // Webcams
            ['Webcam Logitech C920 Full HD', 'Webcams', 'Logitech', 69990, 'Cámara web 1080p a 30 fps con micrófonos estéreo.'],
            ['Webcam Logitech Brio 4K', 'Webcams', 'Logitech', 189990, 'Cámara web 4K con HDR y reconocimiento facial.'],
            ['Webcam Razer Kiyo Pro', 'Webcams', 'Razer', 149990, 'Cámara web 1080p a 60 fps con sensor de luz adaptable.'],
            ['Webcam Elgato Facecam MK.2', 'Webcams', 'Elgato', 159990, 'Cámara web para streaming con controles manuales de imagen.'],
            // Micrófonos
            ['Micrófono HyperX QuadCast S', 'Micrófonos', 'HyperX', 139990, 'Micrófono USB con cuatro patrones polares e iluminación RGB.'],
            ['Micrófono Elgato Wave:3', 'Micrófonos', 'Elgato', 159990, 'Micrófono condensador USB con protección contra saturación Clipguard.'],
            ['Micrófono Razer Seiren Mini', 'Micrófonos', 'Razer', 49990, 'Micrófono USB compacto supercardioide para streaming.'],
            ['Micrófono Redragon GM300', 'Micrófonos', 'Redragon', 39990, 'Micrófono USB con brazo articulado y filtro antipop.'],
            // Mousepads
            ['Mousepad Logitech G640 Grande', 'Mousepads', 'Logitech', 34990, 'Mousepad de tela de 46 x 40 cm con base de goma.'],
            ['Mousepad Razer Gigantus V2 XXL', 'Mousepads', 'Razer', 39990, 'Mousepad de escritorio de 94 x 41 cm.'],
            ['Mousepad SteelSeries QcK Heavy', 'Mousepads', 'SteelSeries', 29990, 'Mousepad grueso de 6 mm con superficie de microtextura.'],
            ['Mousepad HyperX Pulsefire Mat RGB', 'Mousepads', 'HyperX', 49990, 'Mousepad con borde iluminado RGB y superficie de tela.'],
        ];

        foreach ($perifericos as [$nombre, $sub, $marca, $precio, $descripcion]) {
            $this->crear($nombre, $sub, $marca, $precio, $descripcion);
        }
    }
}
