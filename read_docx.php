<?php
$file = __DIR__ . '/assets/menu/MENIU anpc.docx';
if (!file_exists($file)) {
    die("File not found: $file");
}
$zip = new ZipArchive;
if ($zip->open($file) === TRUE) {
    if (($index = $zip->locateName('word/document.xml')) !== false) {
        $content = $zip->getFromIndex($index);
        // Remove XML tags to get raw text, but maybe keep some structure?
        // Let's just get any text content for now.
        $xml = strip_tags($content);
        // Or better, use DOMDocument to get paragraphs
        $dom = new DOMDocument();
        // Libxml error suppression as word xml might have namespaces/errors
        libxml_use_internal_errors(true); 
        $dom->loadXML($content);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        
        // Let's try to extract table rows if it's a menu
        $rows = $xpath->query('//w:tr');
        if ($rows->length > 0) {
            foreach ($rows as $row) {
                $cells = $xpath->query('.//w:tc', $row);
                $rowData = [];
                foreach ($cells as $cell) {
                   $rowData[] = trim($cell->textContent);
                }
                echo implode(" | ", $rowData) . "\n";
            }
        } else {
             // If no tables, just dump paragraphs
             $paras = $dom->getElementsByTagName('p'); // This might be namespaced w:p
             if ($paras->length == 0) {
                 $paras = $xpath->query('//w:p');
             }
             foreach ($paras as $p) {
                 echo $p->textContent . "\n";
             }
        }
    } else {
        echo "Could not find word/document.xml";
    }
    $zip->close();
} else {
    echo 'failed to open docx';
}
?>
