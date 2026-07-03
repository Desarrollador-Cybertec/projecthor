<?php

declare(strict_types=1);

namespace App\Support\Export;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class XlsxExporter
{
    /**
     * Build a real .xlsx file and return it as a download that cleans
     * itself up after being sent.
     *
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|int|float|null>>  $rows
     */
    public function download(string $filename, array $headers, iterable $rows): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');

        $writer = new Writer;
        $writer->openToFile($path);

        $headerStyle = (new Style)->withFontBold(true);
        $writer->addRow(Row::fromValuesWithStyle($headers, $headerStyle));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues(array_map(
                fn ($value) => $value ?? '',
                $row,
            )));
        }

        $writer->close();

        return response()
            ->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend();
    }
}
