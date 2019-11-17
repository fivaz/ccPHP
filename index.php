<?php

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

    $format = $_POST['format'];
    if ($format == "PDF") {
        genererPDF($voitures);
    }
}

function recherche($marque_ou_modele, $annee, $modele_ancien, $impot_max, $assurance_max, $tri)
{
    $dsn = "mysql:host=localhost;dbname=BasesCC";
    $user = "root";
    $passwd = "";

    $pdo = new PDO($dsn, $user, $passwd);

    $query = "SELECT * FROM voitures WHERE ";

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
    $fpdf = new FPDF();
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
                    <td><?= $voiture['impot'] + $voiture['assurance'] ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>

    <?php endif ?>
</div>

</body>

</html>
