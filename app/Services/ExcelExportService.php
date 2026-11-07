<?php

namespace App\Services;

use App\Models\Po;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    /**
     * Export Delivery Order (DO) per PO using the master template.
     */
    public function exportDeliveryOrder(Po $po): StreamedResponse
    {
        $templatePath = config('po.template_path', storage_path('app/templates/template.xlsx'));

        if (! file_exists($templatePath)) {
            // Fallback if template in storage/app/templates doesn't exist
            $templatePath = storage_path('app/templates/template.xlsx');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // E5: Tanggal terbit DO (format: dd-MMM-yyyy, cth: 15-Jan-2025)
        $tglTerbitDo = $po->tgl_terbit_do ? Carbon::parse($po->tgl_terbit_do)->format('d-M-Y') : '';
        $sheet->setCellValue('E5', $tglTerbitDo);

        // E6: Nomor DO
        $sheet->setCellValue('E6', $po->do ?? '');

        // B12: Tanggal PO
        $tglPo = $po->tgl_po ? Carbon::parse($po->tgl_po)->format('d-M-Y') : '';
        $sheet->setCellValue('B12', $tglPo);

        // F12: Nomor PO
        $sheet->setCellValue('F12', $po->po ?? '');

        // O12: Alamat singkat
        $sheet->setCellValue('O12', $po->alamat ?? '');

        // U12: Nama-nama kontak (digabung separator /)
        $contactNames = collect($po->contacts ?? [])
            ->pluck('contact')
            ->filter()
            ->join(' / ');
        $sheet->setCellValue('U12', $contactNames);

        // X4: RichText -> Nama kostumer (Bold, Franklin Gothic Medium size 9) + \n + Alamat lengkap (Normal size 9)
        $richTextCustomer = new RichText;
        $nameRun = $richTextCustomer->createTextRun(($po->kostumer ?? '')."\n");
        $nameRun->getFont()->setBold(true)->setName('Franklin Gothic Medium')->setSize(9);

        $addressText = $po->alamat_lengkap ?: ($po->alamat ?: '');
        $addressRun = $richTextCustomer->createTextRun($addressText);
        $addressRun->getFont()->setBold(false)->setName('Franklin Gothic Medium')->setSize(9);

        $sheet->getCell('X4')->setValue($richTextCustomer);

        // X9: HP. + Nomor-nomor kontak (Bold Tahoma size 9)
        $contactNumbers = collect($po->contacts ?? [])
            ->pluck('no_contact')
            ->filter()
            ->join(' / ');
        $richTextPhone = new RichText;
        $phoneRun = $richTextPhone->createTextRun('HP. '.$contactNumbers);
        $phoneRun->getFont()->setBold(true)->setName('Tahoma')->setSize(9);
        $sheet->getCell('X9')->setValue($richTextPhone);

        // Item Rows (hanya yang berstatus ready: true, mulai baris 16)
        $items = collect($po->items ?? [])->filter(fn ($item) => ! empty($item['ready']));
        $currentRow = 16;
        $counter = 1;

        foreach ($items as $item) {
            $sheet->setCellValue("B{$currentRow}", $counter++);
            $sheet->setCellValue("K{$currentRow}", $item['nama_barang'] ?? '');
            $sheet->setCellValue("W{$currentRow}", $item['qty'] ?? '');
            $sheet->setCellValue("Y{$currentRow}", $item['satuan'] ?? '');
            $currentRow++;
        }

        $rawIdentifier = (string) ($po->do ?: $po->po);
        $safeIdentifier = preg_replace('/[\\\\\/:\*\?"<>\|\r\n\t]/', '-', trim($rawIdentifier));
        $safeIdentifier = trim(preg_replace('/-+/', '-', (string) $safeIdentifier), '-');
        $filename = 'DO-'.($safeIdentifier ?: 'EXPORT').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export all POs to Excel spreadsheet with sheet "PO Masuk".
     */
    public function exportAllPo(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PO Masuk');

        // Header columns:
        // No | Tgl | Estimasi Pengiriman + 6 hari | Aktual Tanggal Pengiriman | Delivery Date | Jeda Hari | PO | Kostumer | Nama Barang | Qty | Harga | Jumlah | PPN 11% | Total | Ekspedisi
        $headers = [
            'A1' => 'No',
            'B1' => 'Tgl',
            'C1' => 'Estimasi Pengiriman + 6 hari',
            'D1' => 'Aktual Tanggal Pengiriman',
            'E1' => 'Delivery Date',
            'F1' => 'Jeda Hari',
            'G1' => 'PO',
            'H1' => 'Kostumer',
            'I1' => 'Nama Barang',
            'J1' => 'Qty',
            'K1' => 'Harga',
            'L1' => 'Jumlah',
            'M1' => 'PPN 11%',
            'N1' => 'Total',
            'O1' => 'Ekspedisi',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header styling: Background kuning, font bold, border tipis
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Freeze header row 1
        $sheet->freezePane('A2');

        // Fetch POs
        $pos = Po::orderBy('id', 'desc')->get();
        $row = 2;
        $no = 1;

        foreach ($pos as $po) {
            // Tgl
            $tgl = $po->tgl_po ? Carbon::parse($po->tgl_po)->format('d/m/Y') : '-';

            // Estimasi Pengiriman + 6 hari from tgl_terima_po
            $estimasi = '-';
            if ($po->tgl_terima_po) {
                $estimasi = Carbon::parse($po->tgl_terima_po)->addDays(6)->format('d/m/Y');
            }

            // Aktual Tanggal Pengiriman (tgl_terbit_do)
            $aktual = $po->tgl_terbit_do ? Carbon::parse($po->tgl_terbit_do)->format('d/m/Y') : '-';

            // Delivery Date
            $delivDate = $po->deleverydate ? Carbon::parse($po->deleverydate)->format('d/m/Y') : '-';

            // Jeda Hari
            $jeda = $po->jedahari !== null ? (string) $po->jedahari : '-';

            // Filter items with ready: true as per spec: "Item yang ditampilkan hanya yang ready: true"
            $allPoItems = collect($po->items ?? []);
            $readyItems = $allPoItems->filter(fn ($item) => ! empty($item['ready']));

            // If no ready item yet, display empty lines or skip? User spec: "Item yang ditampilkan hanya yang ready: true. Multi-items pada satu baris PO digabung menggunakan pemisah baris baru (\n) dengan text wrap aktif."
            $namaBarangList = [];
            $qtyList = [];
            $hargaList = [];
            $jumlahList = [];
            $ppnList = [];
            $totalList = [];

            foreach ($readyItems as $it) {
                $namaBarangList[] = $it['nama_barang'] ?? '-';
                $satuan = ! empty($it['satuan']) ? ' '.$it['satuan'] : '';
                $qtyList[] = ($it['qty'] ?? 0).$satuan;
                $hargaList[] = 'Rp '.number_format((float) ($it['harga'] ?? 0), 0, ',', '.');
                $jumlahList[] = 'Rp '.number_format((float) ($it['jumlah'] ?? 0), 0, ',', '.');
                $ppnList[] = 'Rp '.number_format((float) ($it['ppn'] ?? 0), 0, ',', '.');
                $totalList[] = 'Rp '.number_format((float) ($it['total'] ?? 0), 0, ',', '.');
            }

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $tgl);
            $sheet->setCellValue("C{$row}", $estimasi);
            $sheet->setCellValue("D{$row}", $aktual);
            $sheet->setCellValue("E{$row}", $delivDate);
            $sheet->setCellValue("F{$row}", $jeda);
            $sheet->setCellValue("G{$row}", $po->po ?? '-');
            $sheet->setCellValue("H{$row}", $po->kostumer ?? '-');
            $sheet->setCellValue("I{$row}", implode("\n", $namaBarangList));
            $sheet->setCellValue("J{$row}", implode("\n", $qtyList));
            $sheet->setCellValue("K{$row}", implode("\n", $hargaList));
            $sheet->setCellValue("L{$row}", implode("\n", $jumlahList));
            $sheet->setCellValue("M{$row}", implode("\n", $ppnList));
            $sheet->setCellValue("N{$row}", implode("\n", $totalList));
            $sheet->setCellValue("O{$row}", $po->ekspedisi ?? '-');

            // Set borders and wrap text
            $rowRange = "A{$row}:O{$row}";
            $sheet->getStyle($rowRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($rowRange)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Auto size columns
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'PO-Masuk-'.date('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
