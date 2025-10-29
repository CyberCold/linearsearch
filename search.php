<?php
// ============================================
// ФУНКЦИОНАЛ ПОИСКА
// ============================================

// Генерация случайного массива
function generateRandomArray($size = 60) {
    $arr = [];
    for ($i = 0; $i < $size; $i++) {
        $arr[] = rand(1, 1000);
    }
    return $arr;
}

// PHP линейный поиск (100 повторов)
function phpLinearSearch($arr, $target) {
    $foundIndex = -1;
    $start = microtime(true);
    
    for ($k = 0; $k < 100; $k++) {
        $foundIndex = -1;
        for ($i = 0; $i < count($arr); $i++) {
            if ($arr[$i] == $target) {
                $foundIndex = $i;
                break;
            }
        }
    }
    
    $end = microtime(true);
    $duration = ($end - $start) * 1000;
    
    return [
        'index' => $foundIndex,
        'found' => $foundIndex >= 0,
        'time' => number_format($duration, 6)
    ];
}

// PHP поиск с барьером (100 повторов)
function phpBarrierSearch($arr, $target) {
    $foundIndex = -1;
    $start = microtime(true);
    
    for ($k = 0; $k < 100; $k++) {
        $tempArr = $arr;
        $tempArr[] = $target; // Добавляем барьер в конец
        $originalLength = count($arr);
        
        $i = 0;
        while ($tempArr[$i] != $target) {
            $i++;
        }
        
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

// ============================================
// ОБРАБОТКА ЗАПРОСА
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные
    $arrayInput = $_POST['array'] ?? '';
    $target = floatval($_POST['target'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    // Парсим массив
    if (!empty($arrayInput)) {
        $arr = array_map('floatval', array_filter(explode(',', $arrayInput), function($v) {
            return is_numeric(trim($v));
        }));
    } else {
        $arr = generateRandomArray();
    }
    
    // ============================================
    // PHP ПОИСК
    // ============================================
    
    if ($action === 'php_linear') {
        $result = phpLinearSearch($arr, $target);
        ?>
        <div id="result">
            <h3><?= $result['found'] ? '✅ Число найдено!' : '❌ Число не найдено' ?></h3>
            <p><strong>Метод:</strong> PHP Линейный поиск</p>
            <p><strong>Искомое число:</strong> <?= $target ?></p>
            <?php if ($result['found']): ?>
                <p><strong>Позиция:</strong> <?= $result['index'] ?></p>
            <?php endif; ?>
            <p><strong>Время выполнения:</strong> <?= $result['time'] ?> мс (100 повторов)</p>
            <p><strong>Размер массива:</strong> <?= count($arr) ?> элементов</p>
            <div style="background: #0a0a0a; padding: 15px; border-radius: 6px; margin-top: 15px; font-family: monospace; font-size: 13px; overflow-x: auto;">
                <strong>Массив:</strong> [<?= implode(', ', $arr) ?>]
            </div>
        </div>
        <?php
    }
    
    elseif ($action === 'php_barrier') {
        $result = phpBarrierSearch($arr, $target);
        ?>
        <div id="result">
            <h3><?= $result['found'] ? '✅ Число найдено!' : '❌ Число не найдено' ?></h3>
            <p><strong>Метод:</strong> PHP Поиск с барьером</p>
            <p><strong>Искомое число:</strong> <?= $target ?></p>
            <?php if ($result['found']): ?>
                <p><strong>Позиция:</strong> <?= $result['index'] ?></p>
            <?php endif; ?>
            <p><strong>Время выполнения:</strong> <?= $result['time'] ?> мс (100 повторов)</p>
            <p><strong>Размер массива:</strong> <?= count($arr) ?> элементов</p>
            <p style="color: #9c27b0;">🛡️ <strong>Барьер:</strong> элемент добавлен в конец массива для оптимизации</p>
            <div style="background: #0a0a0a; padding: 15px; border-radius: 6px; margin-top: 15px; font-family: monospace; font-size: 13px; overflow-x: auto;">
                <strong>Массив:</strong> [<?= implode(', ', $arr) ?>]
            </div>
        </div>
        <?php
    }
    
    // ============================================
    // JAVASCRIPT ПОИСК
    // ============================================
    
    elseif ($action === 'js_linear') {
        ?>
        <div id="result"></div>
        <script>
        (function() {
            const arr = <?= json_encode($arr) ?>;
            const target = <?= $target ?>;
            
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
            const duration = end - start;
            
            document.getElementById('result').innerHTML = `
                <h3>${foundIndex >= 0 ? '✅ Число найдено!' : '❌ Число не найдено'}</h3>
                <p><strong>Метод:</strong> JavaScript Линейный поиск</p>
                <p><strong>Искомое число:</strong> ${target}</p>
                ${foundIndex >= 0 ? '<p><strong>Позиция:</strong> ' + foundIndex + '</p>' : ''}
                <p><strong>Время выполнения:</strong> ${duration.toFixed(6)} мс (100 повторов)</p>
                <p><strong>Размер массива:</strong> ${arr.length} элементов</p>
                <div style="background: #0a0a0a; padding: 15px; border-radius: 6px; margin-top: 15px; font-family: monospace; font-size: 13px; overflow-x: auto;">
                    <strong>Массив:</strong> [${arr.join(', ')}]
                </div>
            `;
        })();
        </script>
        <?php
    }
    
    elseif ($action === 'js_barrier') {
        ?>
        <div id="result"></div>
        <script>
        (function() {
            const arr = <?= json_encode($arr) ?>;
            const target = <?= $target ?>;
            
            let foundIndex = -1;
            const start = performance.now();
            
            for (let k = 0; k < 100; k++) {
                const tempArr = [...arr, target];
                let i = 0;
                while(tempArr[i] !== target) i++;
                foundIndex = (i < arr.length) ? i : -1;
            }
            
            const end = performance.now();
            const duration = end - start;
            
            document.getElementById('result').innerHTML = `
                <h3>${foundIndex >= 0 ? '✅ Число найдено!' : '❌ Число не найдено'}</h3>
                <p><strong>Метод:</strong> JavaScript Поиск с барьером</p>
                <p><strong>Искомое число:</strong> ${target}</p>
                ${foundIndex >= 0 ? '<p><strong>Позиция:</strong> ' + foundIndex + '</p>' : ''}
                <p><strong>Время выполнения:</strong> ${duration.toFixed(6)} мс (100 повторов)</p>
                <p><strong>Размер массива:</strong> ${arr.length} элементов</p>
                <p style="color: #9c27b0;">🛡️ <strong>Барьер:</strong> элемент добавлен в конец массива для оптимизации</p>
                <div style="background: #0a0a0a; padding: 15px; border-radius: 6px; margin-top: 15px; font-family: monospace; font-size: 13px; overflow-x: auto;">
                    <strong>Массив:</strong> [${arr.join(', ')}]
                </div>
            `;
        })();
        </script>
        <?php
    }
    
    elseif ($action === 'js_analyze') {
        ?>
        <div id="result"></div>
        <div id="animation"></div>
        <script>
        (async function() {
            const arr = <?= json_encode($arr) ?>;
            const target = <?= $target ?>;
            
            const animation = document.getElementById('animation');
            const resultDiv = document.getElementById('result');
            
            animation.innerHTML = '<p style="margin-bottom: 15px; color: #ffb400;">🔍 Визуальный анализ линейного поиска...</p>';
            
            const boxes = arr.map(num => {
                const div = document.createElement('div');
                div.className = 'number-box';
                div.textContent = num;
                animation.appendChild(div);
                return div;
            });

            let found = false;
            const start = performance.now();
            
            for (let i = 0; i < arr.length; i++) {
                boxes[i].classList.add('checking');
                await new Promise(r => setTimeout(r, 200));
                
                if (arr[i] === target) {
                    boxes[i].classList.remove('checking');
                    boxes[i].classList.add('found');
                    found = true;
                    const end = performance.now();
                    
                    resultDiv.innerHTML = `
                        <h3>✅ Линейный поиск завершен</h3>
                        <p><strong>Метод:</strong> JavaScript Визуальный анализ</p>
                        <p><strong>Число ${target} найдено на позиции ${i}</strong></p>
                        <p><strong>Проверено элементов:</strong> ${i + 1} из ${arr.length}</p>
                        <p><strong>Время:</strong> ${(end-start).toFixed(2)} мс (с анимацией)</p>
                    `;
                    break;
                } else {
                    boxes[i].classList.remove('checking');
                    boxes[i].classList.add('notfound');
                }
            }

            if(!found){
                const end = performance.now();
                resultDiv.innerHTML = `
                    <h3 style="color: #ff4444;">❌ Число не найдено</h3>
                    <p><strong>Метод:</strong> JavaScript Визуальный анализ</p>
                    <p><strong>Число ${target} отсутствует в массиве</strong></p>
                    <p><strong>Проверено элементов:</strong> ${arr.length}</p>
                    <p><strong>Время:</strong> ${(end-start).toFixed(2)} мс (с анимацией)</p>
                `;
            }
        })();
        </script>
        <?php
    }
    
    elseif ($action === 'js_analyze_barrier') {
        ?>
        <div id="result"></div>
        <div id="animation"></div>
        <script>
        (async function() {
            const arr = <?= json_encode($arr) ?>;
            const target = <?= $target ?>;
            
            const animation = document.getElementById('animation');
            const resultDiv = document.getElementById('result');
            
            animation.innerHTML = '<p style="margin-bottom: 15px; color: #ffb400;">🔍 Визуальный анализ поиска с барьером...</p>';
            
            const tempArr = [...arr, target];
            const boxes = tempArr.map((num, idx) => {
                const div = document.createElement('div');
                div.className = 'number-box';
                if (idx === arr.length) {
                    div.classList.add('barrier');
                    div.textContent = '🛡️' + num;
                    div.title = 'Барьер';
                } else {
                    div.textContent = num;
                }
                animation.appendChild(div);
                return div;
            });

            const start = performance.now();
            let i = 0;
            
            while(tempArr[i] !== target) {
                boxes[i].classList.add('checking');
                await new Promise(r => setTimeout(r, 200));
                boxes[i].classList.remove('checking');
                boxes[i].classList.add('notfound');
                i++;
            }
            
            boxes[i].classList.add('checking');
            await new Promise(r => setTimeout(r, 200));
            boxes[i].classList.remove('checking');
            boxes[i].classList.add('found');
            
            const end = performance.now();
            
            if (i < arr.length) {
                resultDiv.innerHTML = `
                    <h3>✅ Поиск с барьером завершен</h3>
                    <p><strong>Метод:</strong> JavaScript Анализ барьера</p>
                    <p><strong>Число ${target} найдено на позиции ${i}</strong></p>
                    <p><strong>Проверено элементов:</strong> ${i + 1} из ${arr.length}</p>
                    <p><strong>Время:</strong> ${(end-start).toFixed(2)} мс (с анимацией)</p>
                    <p style="color: #9c27b0;">🛡️ <strong>Барьер сработал:</strong> элемент найден до барьера</p>
                `;
            } else {
                resultDiv.innerHTML = `
                    <h3 style="color: #ff4444;">❌ Число не найдено в исходном массиве</h3>
                    <p><strong>Метод:</strong> JavaScript Анализ барьера</p>
                    <p><strong>Найден только барьер на позиции ${i}</strong></p>
                    <p><strong>Проверено элементов:</strong> ${i + 1} (включая барьер)</p>
                    <p><strong>Время:</strong> ${(end-start).toFixed(2)} мс (с анимацией)</p>
                    <p style="color: #9c27b0;">🛡️ <strong>Барьер сработал:</strong> поиск остановлен на барьере</p>
                `;
            }
        })();
        </script>
        <?php
    }
}
?>
