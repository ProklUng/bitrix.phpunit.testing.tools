# Кастомные инструменты для PHPUnit тестов под Битрикс

**INTERNAL**

## Установка

`composer require --dev proklung/bitrix-phpunit-testing-tools`

В пакете, использующем этот пакет как зависимость, в composer.json должна быть секция (нужно, чтобы модуль миграций установился куда надо):

```json
    "extra": {
        "installer-paths": {
            "vendor/sheerockoff/bitrix-ci/files/bitrix/modules/{$name}/": ["type:bitrix-module"]
        }
    }
```

## Нюансы

Базовый класс для тестов - `BitrixableTestCase`. Запускает, приложенный к пакету Битрикс и позволяет использовать в тестах
его API.

Параметры доступа к БД определяются в методе `setupDatabaseData` базового класса `BitrixableTestCase`.

Если база на момент запуска не существует, то будет создана.

По умолчанию:

```php
    protected function setupDatabaseData() : void
    {
        putenv('MYSQL_HOST=localhost');
        putenv('MYSQL_DATABASE=bitrix_ci');
        putenv('MYSQL_USER=root');
        putenv('MYSQL_PASSWORD=');
    }
```

Можно переопределить в каждом конкретном тесте.

### Управление

#### Трэйт ResetDatabaseTrait

Указание сбрасывать базу перед каждым тестом и загружать по новой.

#### Трэйт CustomDumpTrait

Сбрасывать базу и загружать кастомный дамп базы. 

Путь к дампу указывается в методе `getDumpPath` теста:

```php
    protected function getDumpPath() : string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/Tests/dump/dump.sql';
    }

```
Действует только в сочетании с `ResetDatabaseTrait`.

При использовании дампов с боя обычно возникают проблемы с просроченностью лицензии. В таком случае приходится вручную
заменить файл `include.php` в `vendor/sheerockoff/bitrix-ci/files/bitrix/modules/main`.

#### Трэйт SprintMigrationsTrait

Использование миграций модуля [sprint.option](https://github.com/andreyryabin/sprint.migration).

Путь к директории с миграциями указывается в методе `getPathSprintMigrations` теста:

```php
    protected function getPathSprintMigrations() : string
    {
        return __DIR__ . '../../../../../../Tests/sprint_migrations/';
    }
```

##### Нюансы

В силу особенностей использования сборки [Bitrix CI](https://github.com/sheerockoff/bitrix-ci) приходится устанавливать модуль хитро - прямо внутрь пакета сборки
(в папку `vendor/sheerockoff/bitrix-ci/files/bitrix/modules/sprint.option`) на стадии работы композера. Побочный эффект - если обновится сама сборка, 
то модуль миграций улетит в трубу. 

Пока так. 

Периодически база тухнет по сроку годности ("... пробной версии истек ..."). Чтобы пофиксить нужно 
запустить любой тест с трэйтом `ResetDatabaseTrait`, пересоздающим базу заново.

#### Трэйт UseMigrationsTrait

Указание запускать миграции перед каждым тестом. 

Под капотом урезанная версия [пакета](https://github.com/arrilot/bitrix-migrations), так что подходят миграции и от него. 
За одним исключением - миграция наследуется не от класса `Arrilot\BitrixMigrations\BaseMigrations\BitrixMigration`, а от 
`Arrilot\BitrixMigrationsFork\BaseMigrations\BitrixMigration`.

Путь к директории с миграциями указывается в методе `getMigrationsDir` теста:

```php
    protected function getMigrationsDir() : string
    {
        return __DIR__ . '/../migrations';
    }
```

К трэйту приложен метод-хелпер `makeMigration` для создания миграций по шаблону.

```php
    protected function makeMigration(string $name, string $template) : void
```

Имеющиеся шаблоны:

<table>
<tr><th>Название</th><th>Описание</th><th>Алиасы</th></tr>
<tr>
    <td>`default`</td>
    <td>Чистый шаблон по умолчанию</td>
    <td></td>
</tr>
<tr>
    <td>`add_iblock_type`</td>
    <td>Добавление типа инфоблока</td>
    <td></td>
</tr>
<tr>
    <td>`add_iblock`</td>
    <td>Добавление инфоблока</td>
    <td></td>
</tr>
<tr>
    <td>`add_iblock_element_property`</td>
    <td>Добавление свойства в инфоблок</td>
    <td>`add_iblock_prop`, `add_iblock_element_prop`, `add_element_prop`, `add_element_property`</td>
</tr>
<tr>
    <td>`add_uf`</td>
    <td>Добавление UF свойства</td>
    <td></td>
</tr>
<tr>
    <td>`query`</td>
    <td>Произвольный запрос в БД через АПИ d7</td>
    <td></td>
</tr>
<tr>
    <td>`add_table`</td>
    <td>Создание таблицы через АПИ d7</td>
    <td>`create_table`</td>
</tr>
<tr>
    <td>`delete_table`</td>
    <td>Удаление таблицы через АПИ d7</td>
    <td>`drop_table`</td>
</tr>
</table>

Дополнительный трэйт - `CSVTrait` для импорта CSV файлов (формата экспорта Битрикса) в миграциях.

После его задействования тест должен реализовать методы:

- ```getIblockCode()``` - код инфоблока;
- ```getImportDefinitionSections()``` - определение подразделов. Массив с номером столбцов в CSV файле (IC_GROUP0 итд);
- ```getImportDefinitionProperties()``` - определение свойств. Массив вида [код свойства => номер столбца в CSV];
- ```getCsvPath()``` - путь к CSV файлу;

**Важно!** - в CSV файле не должно быть первой строчки с названиями столбцов.

### Инвокеры

Из [пакета](https://github.com/worksolutions/bitrix-module-bunit). Переработаны под частные нужды.

#### Нюансы

Для помощи в тестировании кода компонента используется класс ```Prokl\BitrixTestingTools\Invokers\ComponentInvoker```

Методы класса:

- ```__constructor($componentObject)``` - инициализация объекта запуска компонента;
- ```init()``` - инициализация;
- ```setParams($params)``` - устанавливает параметры для запуска тестируемого компонента;
- ```setArParams($params)``` - устанавливает arParams для запуска тестируемого компонента;
- ```setName(string $name)``` - устанавливает название компонента("test.component");
- ```setTemplate($template)``` - устанавливает шаблон компонента("test.component");
- ```execute()``` - запускает компонент на выполнение (шаблон при этом не используется);
- ```getResultValue($name)``` - возвращает параметр $arResult по ключу $name;
- ```getArResult()``` - возвращает полный $arResult работы компонента;
- ```getArResultCached()``` - возвращает кэшированную часть (через $this->__component) $arResult после работы компонента;
- ```getExecuteResult()``` - возвращает результат работы компонента, когда в коде компонента используется оператор возврата ```return```.

Пример:

```php
// ...

/**
 * @label component
 * @test
 */
public function useComponentInvoker() {
    /** @var CBitrixComponent $componentObject */
    $component = new \Prokl\BitrixTestingTools\Invokers\ComponentInvoker($componentObject);
    $component->init();
    $component->setParams(array("id" => 10));
    $component->execute();
    $this->getAssert()->equal($component->getResultValue("id"), 10, "Результат не верен");
}
```

#### Тестирование работы адаптера шаблона (result_modifier)

Тестировать result_modifier шаблона компонента можно объектом класса ```Prokl\BitrixTestingTools\Invokers\ResultModifierInvoker```.

Методы:

- ```__construct($componentName, $template)``` - инициализация объекта, параметры совпадают с параметрами метода ```CMain::IncludeComponent()```;
- ```setArResult($arResult)``` - искусственная установка результата для передачи адаптеру;
- ```setArParams($params)``` - устанавливает arParams для запуска тестируемого компонента;
- ```execute()``` - запуск адаптера на выполнение;
- ```getArResult()``` - возвращает полный ```$arResult``` работы адаптера;
- ```getArResultCached()``` - возвращает кэшированную часть (через $this->__component) $arResult после работы компонента;
- ```getArResultValue($name)``` - значение результата работы адаптера по ключу ```$name```;

Пример:

```php
/**
 * @label component
 * @test
 */
public function modifierForSomeTemplate() {
    $rm = new \Prokl\BitrixTestingTools\Invokers\ResultModifierInvoker("project:test.with.class", "list");
    $rm->setArResult(array("id" => 10));
    $rm->execute();
    $this->getAssert()->equal($rm->getArResultValue("id"), 10, "Параметры не равны");
}
```

#### Тестирование обработки события

Класс ```Prokl\BitrixTestingTools\Invokers\EventInvoker``` облегчает тестирование обработки событий.

Методы:

- ```__construct($module, $eventName)``` - инициализация объекта запуска события, $module - имя модуля выброса события, $eventName - название события;
- ```setExecuteParams($params)``` - установка параметров события в виде массива, будут переданы в параметры события;
- ```execute()``` - выброс события;
- ```countOfHandlers()``` - получение количества обработчиков события;
- ```getEvent()``` - получение объекта события;

Пример:

```php
// ...

/**
 * @test
 */
public function handlersOfEventExist() {
    $eventInvoker = new \Prokl\BitrixTestingTools\Invokers\EventInvoker("main", "OnPageStart");
    $eventInvoker->setExecuteParams(array(
        "IBLOCK_ID" => 12
    ));
    $eventInvoker->execute();

    $this->getAssert()->asTrue($eventInvoker->countOfHandlers() > 1);
}
```

#### Прочее

- Метод `goTo` класса `BitrixableTestCase`. Эмулирует нахождение на каком-либо URL. Выставляет все, что связано с URL
в старом ядре и D7.

Также подменяет автоматом все, что возможно из супер-глобалов типа $_SERVER, $_POST и т.д.

```php
    $_GET['test'] = 'OK';

    $this->goTo('/test/');

    $url = $APPLICATION->GetCurPage(); // $url = '/test/index.php'

    $request = Application::getInstance()->getContext()->getRequest();
    $uriString = $request->getRequestUri(); // $uriString = '/test/'

    $testGetParam = $request->getQuery('test'); // $testGetParam = 'OK'
```
