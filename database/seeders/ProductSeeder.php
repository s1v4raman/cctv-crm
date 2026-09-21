<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'HK-2MP-BULLET',
                'name' => 'Hikvision 2 MP Bullet Camera',
                'description' => '2 MP outdoor IR bullet CCTV camera',
                'unit' => 'Nos',
                'unit_price' => 2200,
            ],
            [
                'sku' => 'HK-4MP-BULLET',
                'name' => 'Hikvision 4 MP Bullet Camera',
                'description' => '4 MP outdoor IR bullet CCTV camera',
                'unit' => 'Nos',
                'unit_price' => 3500,
            ],
            [
                'sku' => 'HK-4MP-DOME',
                'name' => 'Hikvision 4 MP Dome Camera',
                'description' => '4 MP indoor dome CCTV camera',
                'unit' => 'Nos',
                'unit_price' => 3600,
            ],
            [
                'sku' => 'DAH-4MP-IP-BULLET',
                'name' => 'Dahua 4 MP IP Bullet Camera',
                'description' => '4 MP IP bullet camera with IR night vision',
                'unit' => 'Nos',
                'unit_price' => 3400,
            ],
            [
                'sku' => 'HK-5MP-COLORVU',
                'name' => 'Hikvision 5 MP ColorVu Bullet Camera',
                'description' => '5 MP full-color night-vision CCTV bullet camera',
                'unit' => 'Nos',
                'unit_price' => 5200,
            ],
            [
                'sku' => 'HK-2MP-PTZ',
                'name' => 'Hikvision 2 MP PTZ Camera',
                'description' => '2 MP PTZ speed dome camera with remote pan, tilt and zoom',
                'unit' => 'Nos',
                'unit_price' => 18000,
            ],
            [
                'sku' => 'HK-4MP-WIFI',
                'name' => 'Hikvision 4 MP Wi-Fi Camera',
                'description' => '4 MP Wi-Fi indoor camera with mobile monitoring',
                'unit' => 'Nos',
                'unit_price' => 3900,
            ],
            [
                'sku' => 'DVR-4CH',
                'name' => '4 Channel DVR',
                'description' => '4 channel DVR for analog HD CCTV cameras',
                'unit' => 'Nos',
                'unit_price' => 4200,
            ],
            [
                'sku' => 'DVR-8CH',
                'name' => '8 Channel DVR',
                'description' => '8 channel DVR for analog HD CCTV cameras',
                'unit' => 'Nos',
                'unit_price' => 6500,
            ],
            [
                'sku' => 'NVR-8CH',
                'name' => '8 Channel NVR',
                'description' => '8 channel network video recorder for IP cameras',
                'unit' => 'Nos',
                'unit_price' => 8500,
            ],
            [
                'sku' => 'NVR-16CH',
                'name' => '16 Channel NVR',
                'description' => '16 channel NVR for IP cameras',
                'unit' => 'Nos',
                'unit_price' => 12500,
            ],
            [
                'sku' => 'NVR-32CH',
                'name' => '32 Channel NVR',
                'description' => '32 channel NVR for large IP camera installations',
                'unit' => 'Nos',
                'unit_price' => 24000,
            ],
            [
                'sku' => 'HDD-SURV-1TB',
                'name' => '1 TB Surveillance Hard Disk',
                'description' => '1 TB hard disk for CCTV DVR/NVR recording',
                'unit' => 'Nos',
                'unit_price' => 4800,
            ],
            [
                'sku' => 'HDD-SURV-2TB',
                'name' => '2 TB Surveillance Hard Disk',
                'description' => '2 TB hard disk for CCTV DVR/NVR recording',
                'unit' => 'Nos',
                'unit_price' => 6500,
            ],
            [
                'sku' => 'HDD-SURV-4TB',
                'name' => '4 TB Surveillance Hard Disk',
                'description' => '4 TB hard disk for CCTV DVR/NVR recording',
                'unit' => 'Nos',
                'unit_price' => 9800,
            ],
            [
                'sku' => 'HDD-SURV-6TB',
                'name' => '6 TB Surveillance Hard Disk',
                'description' => '6 TB hard disk for CCTV DVR/NVR recording',
                'unit' => 'Nos',
                'unit_price' => 14500,
            ],
            [
                'sku' => 'POE-8PORT',
                'name' => '8 Port PoE Switch',
                'description' => '8 port PoE switch for IP cameras',
                'unit' => 'Nos',
                'unit_price' => 5500,
            ],
            [
                'sku' => 'POE-16PORT',
                'name' => '16 Port PoE Switch',
                'description' => '16 port PoE switch for IP cameras',
                'unit' => 'Nos',
                'unit_price' => 10500,
            ],
            [
                'sku' => 'POE-24PORT-GIG',
                'name' => '24 Port Gigabit PoE Switch',
                'description' => '24 port managed PoE switch for large camera systems',
                'unit' => 'Nos',
                'unit_price' => 18500,
            ],
            [
                'sku' => 'TPLINK-SW-8G',
                'name' => 'TP-Link 8 Port Gigabit Switch',
                'description' => '8 port unmanaged gigabit LAN switch',
                'unit' => 'Nos',
                'unit_price' => 2400,
            ],
            [
                'sku' => 'TPLINK-ROUTER-GIG',
                'name' => 'TP-Link Gigabit Router',
                'description' => 'Gigabit Wi-Fi router for remote CCTV access',
                'unit' => 'Nos',
                'unit_price' => 2800,
            ],
            [
                'sku' => 'TPLINK-OMADA-AP',
                'name' => 'TP-Link Omada Access Point',
                'description' => 'Business ceiling-mount Wi-Fi access point',
                'unit' => 'Nos',
                'unit_price' => 6200,
            ],
            [
                'sku' => 'CABLE-CAT6',
                'name' => 'CAT6 Cable',
                'description' => 'CAT6 network cable supply and laying',
                'unit' => 'Mtr',
                'unit_price' => 20,
            ],
            [
                'sku' => 'CABLE-COAX-3-1',
                'name' => 'Coaxial CCTV Cable',
                'description' => '3+1 coaxial cable for analog CCTV camera installation',
                'unit' => 'Mtr',
                'unit_price' => 25,
            ],
            [
                'sku' => 'RJ45-CONNECTOR',
                'name' => 'RJ45 Connector',
                'description' => 'RJ45 connector and cable termination',
                'unit' => 'Nos',
                'unit_price' => 15,
            ],
            [
                'sku' => 'CCTV-JBOX',
                'name' => 'CCTV Junction Box',
                'description' => 'Weatherproof camera mounting and cable termination box',
                'unit' => 'Nos',
                'unit_price' => 180,
            ],
            [
                'sku' => 'CCTV-PSU-12V',
                'name' => '12V CCTV Power Supply',
                'description' => '12V DC power supply for analog CCTV cameras',
                'unit' => 'Nos',
                'unit_price' => 650,
            ],
            [
                'sku' => 'UPS-1KVA',
                'name' => '1 kVA UPS',
                'description' => 'UPS backup power for CCTV recorder, switch and router',
                'unit' => 'Nos',
                'unit_price' => 5200,
            ],
            [
                'sku' => 'RACK-WALL-6U',
                'name' => 'Wall Mount Network Rack 6U',
                'description' => '6U wall mount rack for NVR, switch and cable management',
                'unit' => 'Nos',
                'unit_price' => 3800,
            ],
            [
                'sku' => 'RACK-WALL-9U',
                'name' => 'Wall Mount Network Rack 9U',
                'description' => '9U wall mount rack for CCTV and networking equipment',
                'unit' => 'Nos',
                'unit_price' => 5200,
            ],
            [
                'sku' => 'SERVICE-CAM-INSTALL',
                'name' => 'CCTV Camera Installation Charge',
                'description' => 'Camera mounting, cable connection, angle adjustment and testing',
                'unit' => 'Nos',
                'unit_price' => 500,
            ],
            [
                'sku' => 'SERVICE-CCTV-CONFIG',
                'name' => 'CCTV Installation and Configuration',
                'description' => 'Camera mounting, cable termination, recorder setup, mobile app setup and testing',
                'unit' => 'Job',
                'unit_price' => 3500,
            ],
            [
                'sku' => 'SERVICE-REMOTE-VIEW',
                'name' => 'Remote Mobile Viewing Setup',
                'description' => 'DVR/NVR internet configuration and mobile app setup',
                'unit' => 'Job',
                'unit_price' => 1000,
            ],
            [
                'sku' => 'SERVICE-RACK-INSTALL',
                'name' => 'Network Rack Installation',
                'description' => 'Wall-mount rack installation and structured cable arrangement',
                'unit' => 'Job',
                'unit_price' => 2500,
            ],
            [
                'sku' => 'SERVICE-SITE-SURVEY',
                'name' => 'CCTV Site Survey',
                'description' => 'Site visit, camera placement plan and material estimation',
                'unit' => 'Job',
                'unit_price' => 1000,
            ],
            [
                'sku' => 'SERVICE-AMC',
                'name' => 'Annual Maintenance Contract',
                'description' => 'One-year CCTV preventive maintenance and support',
                'unit' => 'Job',
                'unit_price' => 6000,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                [
                    'sku' => $product['sku'],
                ],
                [
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'unit' => $product['unit'],
                    'cost_price' => 0,
                    'unit_price' => $product['unit_price'],
                    'is_active' => true,
                ]
            );
        }
    }
}