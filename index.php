<?php
session_start();

//Usata all'interno del php per leggere ogni volta la riga corrente nel csv, così da poter ricavare le varie informazioni come la domanda, le opzioni e la risposta
//L'indexCSV è l'indice in cui è salvato l'url del csv da prendere nella variabile di SESSIONE 'csv_db'
//l'indexLine è l'indice della domanda all'interno del suddetto csv
function readLineFromCSV($indexCSV, $indexLine)
{
    $pointer = fopen($_SESSION['csv_db'][$indexCSV], "r");
    $i = 0;
    while (!feof($pointer) && $i < $indexLine - 1) {
        fgets($pointer);
        $i++;
    }
    $lineStr = fgets($pointer);
    fclose($pointer);
    return $lineStr;
}

//Ritorna la stringa della domanda della Domanda corrente
function takeDomOfTheLine($Line)
{
    return explode(",", $Line)[0];
}


//Prende la stringa della risposta della domanda corrente
function takeAnswerOfTheLine($Line)
{
    return explode(",", $Line)[5];
}

//Ritorna un array di stringhe con le 4 opzioni per rispondere
function takeOptionsOfTheLine($Line)
{
    $str = explode(",", $Line);
    $ret = [$str[1], $str[2], $str[3], $str[4]];
    return $ret;
}

//Ritorna l'indice del csv nella SESSION
function getCSVIndexByDomPosition($domPosition)
{
    $calc = ($domPosition - 1) - (($domPosition - 1) % 5);
    return $calc / 5;
}

//RItorna vero o falso in caso ci sia bisogno di un'immaggine
function theresAnImageInCurrentLine()
{
    return getCSVIndexByDomPosition($_SESSION['currentDomPosition']) == 0 ? true : false;
}

//Ritorna il percorso come stringa dell'immagine della riga
function takeImageOfTheLine($Line)
{
    $imageName = takeAnswerOfTheLine($Line);
    if (str_contains($imageName, ' ')) {
        $imageName = str_replace(' ', '_', $imageName);
    }
    $imageName = trim($imageName);
    return "DATA/IMAGES/$imageName.jpg";
}


if (!isset($_SESSION['array_doms']) || !isset($_SESSION['array_answer'])) {
    $rateDoms = 5;
    $_SESSION['array_doms'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20];
    $_SESSION['array_answer'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20];
    for ($i = 1; $i <= 20; $i++) {
        # code...
        $_SESSION['array_answer'][$i] = "NaN";
    }

    for ($countIndex = 1; $countIndex < 5; $countIndex++) {
        # code...
        $numbers = array();
        while (count($numbers) < 5) {
            $randomNumber = mt_rand(1, 25);
            if (!in_array($randomNumber, $numbers)) {
                $numbers[] = $randomNumber;
            }
        }


        for ($i = ($countIndex - 1) * $rateDoms + 1; $i <= 5 * $countIndex; $i++) {
            $_SESSION['array_doms'][$i] = $numbers[$i % $rateDoms];
        }
    }
    $_SESSION['currentDomPosition'] = 1;
}




if (!isset($_SESSION['csv_db'])) {
    $_SESSION['csv_db'] = ['DATA/CSV/DomDinoImages.csv', 'DATA/CSV/DomDinoLuogo.csv', 'DATA/CSV/DomDinoPale.csv', 'DATA/CSV/DomDinoCur.csv'];
}
if (isset($_POST['indexSelector'])) {
    $_SESSION['currentDomPosition'] = $_POST['indexSelector'];
}

$_SESSION['currentLine'] = readLineFromCSV(getCSVIndexByDomPosition($_SESSION['currentDomPosition']), $_SESSION['array_doms'][$_SESSION['currentDomPosition']]);

if (isset($_POST['lastIndexSelector'])) {
    //echo "Domanda ". $_POST['lastIndexSelector'] . "<br>"; 
    if (isset($_POST['questionsGroup'])) {
        //echo "Risposta ". $_POST['questionsGroup'];
        $_SESSION['array_answer'][$_POST['lastIndexSelector']] = $_POST['questionsGroup'];
        //echo "Risposte: <br>";
        /*for ($i=1; $i <= 20; $i++) { 
            # code...
            if($_SESSION['array_answer'][$i] != "NaN"){
                echo $_SESSION['array_answer'][$i] . "<br>";
            }else{
                echo "NaN"  . "<br>";
            }
        }*/
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoQuiz</title>
    <link rel="stylesheet" href="bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- header -->
    <header>
        <div class="container-fluid text-center">
            <div class="row p-2">
                <div class="col-1 text-start">
                    <img src="DATA/UTILITIES/Logo.svg" alt="logo dinosauro" class="logo">
                </div>
                <div class="col-10">
                    <h1>Dino Quiz</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main section -->
    <div class="container-xl p-3">
        <div class="row">
            <div class="col-4">
                <?php if (theresAnImageInCurrentLine() == true) {
                    $pathImage = takeImageOfTheLine($_SESSION['currentLine']);
                    echo "<img src=$pathImage alt='' class='img-fluid questionImage'>";
                } ?>
            </div>
            <div class="col-8">
                <div class="row">
                    <h1 class="display-3">
                        <?php $perc = getCSVIndexByDomPosition($_SESSION['currentDomPosition']);
                        switch ($perc) {
                            case 0:
                                echo "Indovina";
                                break;
                            case 1:
                                echo "Luoghi";
                                break;
                            case 2:
                                echo "Paleologia";
                                break;
                            case 3:
                                echo "Curiosità";
                                break;
                            default:
                                break;
                        }
                        ?>
                    </h1>
                </div>
                <div class="row">
                    <p>
                        <?php echo takeDomOfTheLine($_SESSION['currentLine']); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="row justify-content-end">
            <div class="col-8">
                <form action="index.php" method="post" class="row row-cols-2 text-start gridContainer">
                    <div class="col">
                        <label for="q1" class="answer">
                            <input class="" type="radio" name="questionsGroup" value="1" id="q1" <?php if ($_SESSION['array_answer'][$_SESSION['currentDomPosition']] == "1") {
                                echo "checked='checked'";
                            }
                            ?>>
                            <?php echo takeOptionsOfTheLine($_SESSION['currentLine'])[0]; ?>
                        </label>
                    </div>
                    <div class="col">
                        <label for="q2" class="answer">
                            <input class="" type="radio" name="questionsGroup" value="2" id="q2" <?php if ($_SESSION['array_answer'][$_SESSION['currentDomPosition']] == "2") {
                                echo "checked='checked'";
                            }
                            ?>>
                            <?php echo takeOptionsOfTheLine($_SESSION['currentLine'])[1]; ?>
                        </label>
                    </div>
                    <div class="col">
                        <label for="q3" class="answer">
                            <input class="" type="radio" name="questionsGroup" value="3" id="q3" <?php if ($_SESSION['array_answer'][$_SESSION['currentDomPosition']] == "3") {
                                echo "checked='checked'";
                            }
                            ?>>
                            <?php echo takeOptionsOfTheLine($_SESSION['currentLine'])[2]; ?>
                        </label>
                    </div>
                    <div class="col">
                        <label for="q4" class="answer">
                            <input class="" type="radio" name="questionsGroup" value="4" id="q4" <?php if ($_SESSION['array_answer'][$_SESSION['currentDomPosition']] == "4") {
                                echo "checked='checked'";
                            }
                            ?>>
                            <?php echo takeOptionsOfTheLine($_SESSION['currentLine'])[3]; ?>
                        </label>
                    </div>

                    <div class="d-inline-flex justify-content-between fixed-bottom pb-5 w-100">
                        <input type="submit" class="moveBtn" value="back">
                        <input type="submit" class="moveBtn" value="next">
                    </div>

                    <div class="row">
                        <!-- progress -->
                        <div class="fixed-bottom container-fluid p-0 selector">
                            <div class="justify-content-evenly d-flex">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="1">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="2">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="3">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="4">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="5">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="6">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="7">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="8">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="9">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="10">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="11">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="12">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="13">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="14">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="15">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="16">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="17">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="18">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="19">
                                <input type="submit" name="indexSelector" class="btn w-100 d-inline-flex selector-btn"
                                    value="20">
                                <input type="hidden" name="lastIndexSelector"
                                    value="<?php echo $_SESSION['currentDomPosition']; ?>">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script src="bootstrap/bootstrap.min.js"></script>
</body>

</html>