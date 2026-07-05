<?php
require __DIR__ . '/vendor/autoload.php';

$html = file_get_contents(__DIR__ . '/docs_content.html');

$mpdf = new \Mpdf\Mpdf([
    'mode'          => 'utf-8',
    'format'        => 'A4',
    'margin_top'    => 18,
    'margin_bottom' => 18,
    'margin_left'   => 14,
    'margin_right'  => 14,
    'default_font'  => 'dejavusans',
    'direction'     => 'rtl',
]);

$mpdf->SetTitle('توثيق مشروع شامسونج');
$mpdf->SetAuthor('Shamsung Team');
$mpdf->WriteHTML($html);
$mpdf->Output(__DIR__ . '/Shamsung_API_Documentation.pdf', 'F');

echo "✅ PDF generated: Shamsung_API_Documentation.pdf\n";
