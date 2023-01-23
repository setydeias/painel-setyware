<?php
    
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/AnalysisRecord.class.php';
    $data = json_decode(file_get_contents('php://input'));
    $our_number = $data->our_number;
    
    $AnalysisRecord = new AnalysisRecord();
    $occurrences = $AnalysisRecord->getOccurrencesByOurNumber(array($our_number));
    
    echo json_encode($occurrences);