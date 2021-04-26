<?php

namespace Prokl\BitrixTestingTools\Traits;

/**
 * Trait BitrixExceptionAssertTrait
 * @package Prokl\BitrixTestingTools\Traits
 *
 * @since 26.04.2021
 */
trait BitrixExceptionAssertTrait
{
    /**
     * Ассерт на битриксовое исключение (по тексту).
     *
     * @param string $message Сообщение в битриксовом исключении.
     *
     * @return void
     */
    protected function willExpectBitrixExceptionMessage(string $message) : void
    {
        $exceptionText = $GLOBALS['APPLICATION']->GetException();

        $this->assertSame(
            $message,
            $exceptionText->GetString()
        );
    }
}
