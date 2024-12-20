<?php
// Fonction pour formater la date
function formaterDate($date) {
    setlocale(LC_TIME, 'fr_FR.UTF8');
    return strftime('%B %d, %Y', strtotime($date));
}
?>
