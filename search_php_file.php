<?php
header('Content-Type: text/html; charset=UTF-8');

// Функция линейного поиска
function linearSearch($arr, $target, $repeats = 100) {
    $foundIndex = -1;
    $start = microtime(true);
    
    for ($k = 0; $k < $repeats; $k++) {
        $foundIndex = -1;
        for ($i = 0; $i < count($arr); $i++) {
            if ($arr[$i] == $target) {
                $foundIndex = $i;
                break;
            }
        }
    }
    
    $end = microtime(true);
    $duration = ($end - $start) * 1000; // в миллисекунды
    
    return [
        'index' => $foundIndex,
        'found' => $foundIndex >= 0,
        'time' => number_format($duration, 6)
    ];
}

// Функция поиска с барьером
function barrierSearch($arr, $target, $repeats = 100) {
    $foundIndex = -1;
    $start = microtime(true);
    
    for ($k = 0; $k < $repeats; $k++) {
        $tempArr = $arr;
        $tempArr[] = $target; // добавляем барьер в конец
        $originalLength = count($arr);
        
        $i = 0;
        while ($tempArr[$i] != $target) {
            $i++;
        }
        
        // Если индекс меньше длины оригинального массива - элемент найден
        $foundIndex = ($i < $originalLength) ? $i : -1;
    }
    
    $end = microtime(true);
    $duration = ($end - $start) * 1000;
    
    return [
        'index' => $foundIndex,
        'found' => $foundIndex >= 0,
        'time' => number_format($duration, 6)
    ];
}

// Обработка POST запросов (для AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['array']) && isset($input['target']) && isset($input['method'])) {
        $arr = $input['array'];
        $target = $input['target'];
        $method = $input['method'];
        
        if ($method === 'barrier') {
            $result = barrierSearch($arr, $target);
        } else {
            $result = linearSearch($arr, $target);
        }
        
        echo json_encode($result);
        exit;
    }
}

// Генерация случайного массива
function generateRandomArray($size = 60) {
    $arr = [];
    for ($i = 0; $i < $size; $i++) {
        $arr[] = rand(1, 1000);
    }
    return $arr;
}

// Обработка формы
$result = null;
$arr = [];
$target = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Парсинг массива
    if (!empty($_POST['array'])) {
        $arr = array_map('floatval', array_filter(explode(',', $_POST['array']), function($v) {
            return is_numeric(trim($v));
        }));
    } else {
        $arr = generateRandomArray();
    }
    
    $target = floatval($_POST['target']);
    
    switch ($_POST['action']) {
        case 'linear':
            $result = linearSearch($arr, $target);
            $result['method'] = 'Линейный поиск (PHP)';
            break;
        case 'barrier':
            $result = barrierSearch($arr, $target);
            $result['method'] = 'Поиск с барьером (PHP)';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск числа - PHP vs JS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #0e0e0e;
            color: #f1f1f1;
            text-align: center;
            padding: 40px;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h2 {
            color: #00aaff;
            margin-bottom: 30px;
        }
        input, button {
            padding: 12px;
            margin: 8px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
        }
        input {
            width: 300px;
            background: #1a1a1a;
            color: #f1f1f1;
            border: 1px solid #333;
        }
        input:focus {
            outline: none;
            border-color: #008cff;
        }
        button {
            cursor: pointer;
            background: #008cff;
            color: #fff;
            transition: 0.2s;
            min-width: 200px;
        }
        button:hover {
            background: #00aaff;
            transform: translateY(-2px);
        }
        .buttons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        .result-box {
            background: #1a1a1a;
            padding: 25px;
            margin: 30px 0;
            border-radius: 8px;
            border-left: 4px solid #00c851;
            text-align: left;
        }
        .result-box.not-found {
            border-left-color: #ff4444;
        }
        .info {
            background: #1a1a1a;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #9c27b0;
            text-align: left;
        }
        .method-badge {
            display: inline-block;
            background: #008cff;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .array-display {
            background: #0a0a0a;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            font-family: monospace;
            overflow-x: auto;
            white-space: nowrap;
        }
        .js-section {
            background: #1a1a2e;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #ffb400;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Поиск числа: PHP vs JavaScript</h2>
        
        <div class="info">
            <strong>📌 Что такое поиск с барьером?</strong><br>
            Поиск с барьером — это оптимизация линейного поиска. Искомый элемент добавляется в конец массива (барьер), 
            что гарантирует его нахождение и позволяет убрать проверку границ массива в цикле while. 
            Это уменьшает количество операций сравнения в каждой итерации.
        </div>

        <form method="POST">
            <input type="text" name="array" placeholder="Введите массив (например: 3,5,8,2,9) или оставьте пустым" 
                   value="<?= isset($_POST['array']) ? htmlspecialchars($_POST['array']) : '' ?>">
            <br>
            <input type="text" name="target" placeholder="Введите искомое число" required
                   value="<?= isset($_POST['target']) ? htmlspecialchars($_POST['target']) : '' ?>">
            <br>
            
            <div class="buttons-grid">
                <button type="submit" name="action" value="linear">🔹 Линейный поиск (PHP)</button>
                <button type="submit" name="action" value="barrier">🔹 Поиск с барьером (PHP)</button>
            </div>
        </form>

        <?php if ($result): ?>
            <div class="result-box <?= $result['found'] ? '' : 'not-found' ?>">
                <div class="method-badge"><?= $result['method'] ?></div>
                <h3><?= $result['found'] ? '✅ Число найдено!' : '❌ Число не найдено' ?></h3>
                <p><strong>Искомое число:</strong> <?= $target ?></p>
                <?php if ($result['found']): ?>
                    <p><strong>Позиция в массиве:</strong> <?= $result['index'] ?></p>
                <?php endif; ?>
                <p><strong>Время выполнения:</strong> <?= $result['time'] ?> мс (100 повторов)</p>
                <p><strong>Размер массива:</strong> <?= count($arr) ?> элементов</p>
                
                <div class="array-display">
                    <strong>Массив:</strong> [<?= implode(', ', $arr) ?>]
                </div>
            </div>
        <?php endif; ?>

        <div class="js-section">
            <h3>🟨 JavaScript версия (клиентская)</h3>
            <div class="buttons-grid">
                <button onclick="searchJS()">Линейный поиск (JS)</button>
                <button onclick="searchBarrierJS()">Поиск с барьером (JS)</button>
            </div>
            <div id="js-result" style="margin-top: 20px;"></div>
        </div>
    </div>

    <script>
        function getArray() {
            const input = document.querySelector('input[name="array"]').value;
            if (!input.trim()) {
                return Array.from({length: 50 + Math.floor(Math.random()*21)}, () => Math.floor(Math.random()*1000));
            }
            return input.split(',').map(n => parseFloat(n.trim())).filter(n => !isNaN(n));
        }

        function searchJS() {
            const arr = getArray();
            const target = parseFloat(document.querySelector('input[name="target"]').value);
            if(isNaN(target)) { alert('Введите число!'); return; }
            
            let foundIndex = -1;
            const start = performance.now();
            for (let k = 0; k < 100; k++) {
                foundIndex = -1;
                for (let i = 0; i < arr.length; i++) {
                    if (arr[i] === target) {
                        foundIndex = i;
                        break;
                    }
                }
            }
            const end = performance.now();
            
            document.getElementById('js-result').innerHTML = `
                <div style="background: #0a0a0a; padding: 15px; border-radius: 6px; text-align: left;">
                    <strong>🟨 JS Линейный поиск:</strong><br>
                    Число <b>${target}</b> → ${foundIndex >= 0 ? 'найдено на позиции ' + foundIndex : 'не найдено'}<br>
                    Время: ${(end - start).toFixed(6)} мс (100 повторов)<br>
                    Размер массива: ${arr.length} элементов
                </div>
            `;
        }

        function searchBarrierJS() {
            const arr = getArray();
            const target = parseFloat(document.querySelector('input[name="target"]').value);
            if(isNaN(target)) { alert('Введите число!'); return; }
            
            let foundIndex = -1;
            const start = performance.now();
            for (let k = 0; k < 100; k++) {
                const tempArr = [...arr, target];
                let i = 0;
                while(tempArr[i] !== target) i++;
                foundIndex = (i < arr.length) ? i : -1;
            }
            const end = performance.now();
            
            document.getElementById('js-result').innerHTML = `
                <div style="background: #0a0a0a; padding: 15px; border-radius: 6px; text-align: left;">
                    <strong>🟨 JS Поиск с барьером:</strong><br>
                    Число <b>${target}</b> → ${foundIndex >= 0 ? 'найдено на позиции ' + foundIndex : 'не найдено'}<br>
                    Время: ${(end - start).toFixed(6)} мс (100 повторов)<br>
                    Размер массива: ${arr.length} элементов
                </div>
            `;
        }
    </script>
</body>
</html>