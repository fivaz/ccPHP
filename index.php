<?php

$format = null;

if (isset($_GET['recherche'])) {

    $voitures = [];

    $marque_ou_modele = $_POST['marque-ou-modele'];
    $annee = $_POST['annee'];
    $modele_ancien = null;
    if (isset($_POST['modele-ancien']))
        $modele_ancien = $_POST['modele-ancien'];
    $impot_max = $_POST['impot-max'];
    $assurance_max = $_POST['assurance-max'];
    $tri = null;
    if (isset($_POST['tri']))
        $tri = $_POST['tri'];
    $voitures = recherche($marque_ou_modele, $annee, $modele_ancien, $impot_max, $assurance_max, $tri);

    if (isset($_GET['CSV'])) {
        genererCSV($voitures);
    } else {
        $format = $_POST['format'];
        if ($format == "PDF")
            genererPDF($voitures);
        if ($format == "CSV")
            genererCSV($voitures);
        if ($format == "JSON")
            genererJSON($voitures);
        if ($format == "XML")
            genererXML($voitures);
    }
}

function recherche($marque_ou_modele, $annee, $modele_ancien, $impot_max, $assurance_max, $tri)
{
    $dsn = "mysql:host=localhost;dbname=BasesCC";
    $user = "root";
    $passwd = "";

    $pdo = new PDO($dsn, $user, $passwd);

    $query = "SELECT marque,modele,annee,impot + assurance AS 'cout' FROM voitures WHERE ";

    if ($marque_ou_modele)
        $query .= "(marque LIKE '{$marque_ou_modele}%' OR modele LIKE '{$marque_ou_modele}%') ";
    if ($annee)
        $query .= "AND annee = {$annee} ";
    else if ($modele_ancien)
        $query .= "AND annee <= 2010 ";
    if ($impot_max)
        $query .= "AND impot <= '{$impot_max}' ";
    if ($assurance_max)
        $query .= "AND assurance <= '{$assurance_max}' ";

    $query .= "ORDER BY ";

    if (!$tri || $tri == "modele")
        $query .= "marque ASC, modele ASC";
    else if ($tri == "cout")
        $query .= "(impot + assurance) ASC";

    $stm = $pdo->query($query);

    return $stm->fetchAll(PDO::FETCH_ASSOC);
}

function genererPDF($voitures)
{
    require('fpdf.php');
    $pdf = new FPDF();

    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(20, 10, "Marque", 1);
    $pdf->Cell(30, 10, "Modele", 1);
    $pdf->Cell(20, 10, "Annee", 1);
    $pdf->Cell(50, 10, "Cout(impot + assurance)", 1, 1);
    for ($i = 0; $i < count($voitures); $i++) {
        $voiture = $voitures[$i];
        $pdf->Cell(20, 10, $voiture['marque'], 1);
        $pdf->Cell(30, 10, $voiture['modele'], 1);
        $pdf->Cell(20, 10, $voiture['annee'], 1);
        $pdf->Cell(50, 10, $voiture['cout'], 1, 1);
    }
    $pdf->Output();
}

function genererCSV($voitures)
{
    $fp = fopen('file.csv', 'w');

    fwrite($fp, "marque; modele; annee; coût(impot + assurance);");
    foreach ($voitures as $voiture) {
        fputcsv($fp, $voiture);
    }

    fclose($fp);
}

function genererJSON($voitures)
{
    $fp = fopen('file.txt', 'w');
    fwrite($fp, json_encode($voitures));
    fclose($fp);
}

function genererXML($voitures)
{
    $fp = fopen('xml.txt', 'w');
    foreach ($voitures as $voiture) {
        fwrite($fp, "
                <voiture>
                    <marque>" . $voiture['marque'] . "</marque>
                    <modele>" . $voiture['modele'] . "</modele>
                    <annee>" . $voiture['annee'] . "</annee>
                    <cout>" . $voiture['cout'] . "</cout>
                </voiture>
            ");
    }
    fclose($fp);
}

?>

<html>

<body>

<form action="index.php?recherche=true" method="POST">
    <div>
        <label>marque ou modele</label>
        <input name="marque-ou-modele">
    </div>

    <div>
        <label>année</label>
        <input name="annee">
    </div>

    <div>
        <label>uniquement modeles anciens</label>
        <input name="modele-ancien" type="checkbox">
    </div>

    <div>
        <label>impôt max</label>
        <input name="impot-max">
    </div>

    <div>
        <label>assurance max</label>
        <input name="assurance-max">
    </div>

    <div>
        <p>tri</p>
        <label>modèle</label>
        <input type="radio" name="tri" value="modele">

        <label>année</label>
        <input type="radio" name="tri" value="annee">

        <label>coût</label>
        <input type="radio" name="tri" value="cout">
    </div>

    <div>
        <select name="format">
            <option value="HTML" selected="selected">HTML</option>
            <option value="PDF">PDF</option>
            <option value="CSV">CSV</option>
            <option value="JSON">JSON</option>
            <option value="XML">XML</option>
        </select>
    </div>

    <div>
        <button type="submit">rechercher</button>
    </div>
</form>

<div>

    <?php if ($format == "HTML"): ?>

        <table border="1">
            <thead>
            <th> Marque</th>
            <th> Modèle</th>
            <th> Année</th>
            <th> Coût(impôt + assurance)</th>
            </thead>
            <tbody>
            <?php foreach ($voitures as $voiture): ?>
                <tr>
                    <td><?= $voiture['marque'] ?></td>
                    <td><?= $voiture['modele'] ?></td>
                    <td><?= $voiture['annee'] ?></td>
                    <td><?= $voiture['cout'] ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>

    <?php endif ?>
</div>

</body>

</html>
