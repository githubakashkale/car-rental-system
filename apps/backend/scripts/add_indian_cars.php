<?php
// scripts/add_indian_cars.php — Run once to add Indian cars to existing DB
require __DIR__ . '/../config/db.php';
session_start();
$_SESSION['user_id'] = 1; // Simulate admin for logging

$indianCars = [
    ['Maruti Suzuki', 'Swift', 'Hatchback', 2024, 1800.00, "India's favourite hatchback. Sporty design with excellent mileage.", 'https://imgd.aeplcdn.com/664x374/n/cw/ec/159099/swift-exterior-right-front-three-quarter.jpeg?isig=0&q=80', 'Mumbai'],
    ['Maruti Suzuki', 'Baleno', 'Hatchback', 2024, 2200.00, 'Premium hatchback with advanced features and bold styling.', 'https://imgd.aeplcdn.com/664x374/n/cw/ec/159099/baleno-exterior-right-front-three-quarter.jpeg?isig=0&q=80', 'Pune'],
    ['Hyundai', 'Creta', 'SUV', 2024, 3500.00, 'Best-selling SUV with panoramic sunroof and connected features.', 'https://imgd.aeplcdn.com/664x374/n/cw/ec/106815/creta-exterior-right-front-three-quarter-4.jpeg?isig=0&q=80', 'Delhi'],
    ['Tata', 'Nexon', 'SUV', 2024, 2800.00, '5-star safety rated compact SUV with aggressive styling.', 'https://imgd.aeplcdn.com/664x374/n/cw/ec/141867/nexon-exterior-right-front-three-quarter-75.jpeg?isig=0&q=80', 'Mumbai'],
    ['Tata', 'Harrier', 'SUV', 2024, 4200.00, "Premium SUV built on Land Rover D8 platform.", 'https://imgd.aeplcdn.com/664x374/n/cw/ec/139651/harrier-exterior-right-front-three-quarter.jpeg?isig=0&q=80', 'Bangalore'],
    ['Mahindra', 'Thar', 'SUV', 2024, 3800.00, 'Iconic off-roader for adventure lovers. 4x4 capable.', 'https://imgd.aeplcdn.com/664x374/n/cw/ec/40432/thar-exterior-right-front-three-quarter-40.jpeg?isig=0&q=80', 'Jaipur'],
    ['Mahindra', 'XUV700', 'SUV', 2024, 4500.00, 'Feature-loaded SUV with ADAS, panoramic sunroof, and dual screens.', 'https://imgd.aeplcdn.com/664x374/n/cw/ec/42355/xuv700-exterior-right-front-three-quarter.jpeg?isig=0&q=80', 'Hyderabad'],
    ['Kia', 'Seltos', 'SUV', 2024, 3200.00, 'Stylish SUV with connected car technology and turbocharged engine.', 'https://imgd.aeplcdn.com/664x374/n/cw/ec/174323/seltos-exterior-right-front-three-quarter.jpeg?isig=0&q=80', 'Chennai'],
    ['Hyundai', 'i20', 'Hatchback', 2024, 2000.00, 'Premium hatchback with sunroof, wireless charging, and BlueLink.', 'https://imgd.aeplcdn.com/664x374/n/cw/ec/150605/i20-exterior-right-front-three-quarter-2.jpeg?isig=0&q=80', 'Kolkata'],
    ['Tata', 'Punch', 'SUV', 2024, 1500.00, 'Micro SUV with 5-star safety. Perfect for city drives.', 'https://imgd.aeplcdn.com/664x374/n/cw/ec/107541/punch-exterior-right-front-three-quarter-63.jpeg?isig=0&q=80', 'Ahmedabad'],
];

$added = 0;
foreach ($indianCars as $v) {
    $db->addVehicle($v[0], $v[1], $v[2], $v[3], $v[4], $v[5], $v[6], $v[7]);
    $added++;
    echo "Added: {$v[0]} {$v[1]}\n";
}
echo "\nDone! Added $added Indian cars.\n";
?>
