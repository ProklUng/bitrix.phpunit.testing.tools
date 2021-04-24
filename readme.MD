# Кастомные инструменты для PHPUnit тестов под Битрикс

**INTERNAL**

## Установка

`composer require --dev proklung/bitrix-phpunit-testing-tools`

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

Сбрасывать базу и загружать кастомный дамп базы. Путь к дампу указывается в методе `getDumpPath` теста.

```php
    protected function getDumpPath() : string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/Tests/dump/dump.sql';
    }

```
Действует только в сочетании с `ResetDatabaseTrait`.

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

#### Тестирование работы адаптера шабалона (result_modifier)

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
