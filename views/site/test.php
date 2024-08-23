<?php
$dateStart = (new \DateTime(date('Y-m-d')))->modify('-1 week');
$dateEnd = (new \DateTime(date('Y-m-d')));

$dateInterval = new \DateInterval('P1D');

$datePeriod = new \DatePeriod($dateStart, $dateInterval, $dateEnd);

foreach ($datePeriod as $date) {
    $result[$date->format('Y-m-d')] = sprintf(
        '%s',
        $date->format('d/m/Y'),
    );
}

$dateTest = (new DateTime('2024-08-24'))->format('Y-m-d');

dump($dateTest);



// 1 рубль = 0,008759935 gbp
// 1 рубль = 0,011261769 usd
// 0,008759935 gbp = 0,011261769 usd
// gbp / usd = 0,777847157 | usd = 0,777847157 gbp
// usd / gbp = 1,285599608 | gbp = 1,285599608 usd



// 1 usd = 88,796 rub
// 1 gbp = 114,1561