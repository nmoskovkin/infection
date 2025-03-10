<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config;

use Infection\TestFramework\PhpUnit\Config\Exception\InvalidPhpUnitXmlConfigException;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class XmlConfigurationHelperTest extends TestCase
{
    public function test_it_replaces_with_absolute_paths(): void
    {
        $this->assertItChangesStandardConfiguration(static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
            $xmlconfig->replaceWithAbsolutePaths($xPath);
        }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="/app/autoload2.php"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    syntaxCheck="false"
>
    <testsuites>
        <testsuite name="Application Test Suite">
            <directory>/*Bundle</directory>
            <exclude>/*Bundle/Fixtures</exclude>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist>
            <directory>/src/</directory>
        </whitelist>
    </filter>
    <logging>
        <log type="coverage-html" target="/path/to/tmp"/>
    </logging>
</phpunit>
XML
        );
    }

    public function test_it_removes_existing_loggers(): void
    {
        $this->assertItChangesStandardConfiguration(static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
            $xmlconfig->removeExistingLoggers($xPath);
        }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    syntaxCheck="false"
>
    <testsuites>
        <testsuite name="Application Test Suite">
            <directory>./*Bundle</directory>
            <exclude>./*Bundle/Fixtures</exclude>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist>
            <directory>src/</directory>
        </whitelist>
    </filter>
</phpunit>
XML
        );
    }

    public function test_it_sets_set_stop_on_failure(): void
    {
        $this->assertItChangesStandardConfiguration(static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
            $xmlconfig->setStopOnFailure($xPath);
        }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    stopOnFailure="true"
    syntaxCheck="false"
>
    <testsuites>
        <testsuite name="Application Test Suite">
            <directory>./*Bundle</directory>
            <exclude>./*Bundle/Fixtures</exclude>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist>
            <directory>src/</directory>
        </whitelist>
    </filter>
    <logging>
        <log type="coverage-html" target="/path/to/tmp"/>
    </logging>
</phpunit>
XML
        );
    }

    public function test_it_sets_set_stop_on_failure_when_it_is_already_present(): void
    {
        $this->assertItChangesXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>
XML
        , static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
            $xmlconfig->setStopOnFailure($xPath);
        }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    stopOnFailure="true"
    syntaxCheck="false"
>
</phpunit>
XML
        );
    }

    public function test_it_deactivates_colors(): void
    {
        $this->assertItChangesStandardConfiguration(static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
            $xmlconfig->deactivateColours($xPath);
        }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    colors="false"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    syntaxCheck="false"
>
    <testsuites>
        <testsuite name="Application Test Suite">
            <directory>./*Bundle</directory>
            <exclude>./*Bundle/Fixtures</exclude>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist>
            <directory>src/</directory>
        </whitelist>
    </filter>
    <logging>
        <log type="coverage-html" target="/path/to/tmp"/>
    </logging>
</phpunit>
XML
        );
    }

    public function test_it_deactivates_colors_when_it_is_not_already_present(): void
    {
        $this->assertItChangesXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>
XML
            , static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
                $xmlconfig->deactivateColours($xPath);
            }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    colors="false"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>
XML
        );
    }

    public function test_it_sets_cache_result_to_false_when_it_exists(): void
    {
        $this->assertItChangesXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    cacheResult="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>

XML
        , static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
            $xmlconfig->deactivateResultCaching($xPath);
        }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    cacheResult="false"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>
XML
        );
    }

    public function test_it_sets_cache_result_to_false_when_it_does_not_exist(): void
    {
        $this->assertItChangesXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>
XML
        , static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
            $xmlconfig->deactivateResultCaching($xPath);
        }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    cacheResult="false"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>
XML
        );
    }

    public function test_it_sets_stderr_to_false_when_it_exists(): void
    {
        $this->assertItChangesXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    stderr="true"
    syntaxCheck="false"
>
</phpunit>
XML
        , static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
            $xmlconfig->deactivateStderrRedirection($xPath);
        }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    stderr="false"
    syntaxCheck="false"
>
</phpunit>
XML
        );
    }

    public function test_it_sets_stderr_to_false_when_it_does_not_exist(): void
    {
        $this->assertItChangesXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    syntaxCheck="false"
>
</phpunit>
XML
            , static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
                $xmlconfig->deactivateStderrRedirection($xPath);
            }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    stderr="false"
    syntaxCheck="false"
>
</phpunit>
XML
        );
    }

    public function test_it_removes_existing_printers(): void
    {
        $this->assertItChangesXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>
XML
            , static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
                $xmlconfig->removeExistingPrinters($xPath);
            }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    processIsolation="false"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>
XML
        );
    }

    public function test_it_validates_xml_by_root_node(): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML('<invalid></invalid>');
        $xPath = new \DOMXPath($dom);
        $xmlHelper = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $this->expectException(InvalidPhpUnitXmlConfigException::class);
        $this->expectExceptionMessage('phpunit.xml does not contain a valid PHPUnit configuration.');

        $xmlHelper->validate($xPath);
    }

    /**
     * @dataProvider schemaProvider
     *
     * @group integration Might require an external connection to download the XSD
     */
    public function test_it_validates_xml_by_xsd(string $xsdSchema): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(<<<"XML"
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    xsi:noNamespaceSchemaLocation="$xsdSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
>
    <invalid></invalid>
</phpunit>
XML
        );
        $xPath = new \DOMXPath($dom);
        $xmlHelper = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $this->expectException(InvalidPhpUnitXmlConfigException::class);
        $this->expectExceptionMessageRegExp('/Element \'invalid\'\: This element is not expected/');

        $xmlHelper->validate($xPath);
    }

    public function schemaProvider(): \Generator
    {
        yield 'Remote XSD' => ['https://raw.githubusercontent.com/sebastianbergmann/phpunit/7.4.0/phpunit.xsd'];

        yield 'Local XSD' => ['./vendor/phpunit/phpunit/phpunit.xsd'];
    }

    /**
     * @dataProvider schemaProvider
     *
     * @group integration Might require an external connection to download the XSD
     */
    public function test_it_passes_validation_by_xsd(string $xsdSchema): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    xsi:noNamespaceSchemaLocation="$xsdSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
>
</phpunit>
XML
        );
        $xPath = new \DOMXPath($dom);

        $xmlHelper = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $this->assertTrue($xmlHelper->validate($xPath));
    }

    public function test_it_removes_default_test_suite(): void
    {
        $this->assertItChangesXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    defaultTestSuite="unit"
    printerClass="Fake\Printer\Class"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>

XML
            , static function (XmlConfigurationHelper $xmlconfig, \DOMXPath $xPath, \DOMDocument $dom): void {
                $xmlconfig->removeDefaultTestSuite($xPath);
            }, <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    printerClass="Fake\Printer\Class"
    stopOnFailure="false"
    syntaxCheck="false"
>
</phpunit>

XML
        );
    }

    private function assertItChangesStandardConfiguration(\Closure $callback, string $resultXml): void
    {
        $dom = $this->getDomDocument();
        $xPath = new \DOMXPath($dom);

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $callback($xmlconfig, $xPath, $dom);

        $this->assertXmlStringEqualsXmlString($resultXml, $dom->saveXML());
    }

    private function assertItChangesXML(string $inputXml, callable $callback, string $expectedXml): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($inputXml);
        $xPath = new \DOMXPath($dom);

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $callback($xmlconfig, $xPath, $dom);

        $this->assertXmlStringEqualsXmlString($expectedXml, $dom->saveXML());
    }

    private function getDomDocument(): \DOMDocument
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->getPHPUnitXMLConfig());

        return $dom;
    }

    private function getPathReplacer(): PathReplacer
    {
        return new PathReplacer(new Filesystem());
    }

    private function getPHPUnitXMLConfig(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    backupGlobals="false"
    backupStaticAttributes="false"
    bootstrap="app/autoload2.php"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    printerClass="Fake\Printer\Class"
    processIsolation="false"
    syntaxCheck="false"
>
    <testsuites>
        <testsuite name="Application Test Suite">
            <directory>./*Bundle</directory>
            <exclude>./*Bundle/Fixtures</exclude>
        </testsuite>
    </testsuites>

    <filter>
        <whitelist>
            <directory>src/</directory>
        </whitelist>
    </filter>

    <logging>
        <log type="coverage-html" target="/path/to/tmp"/>
    </logging>
</phpunit>
XML;
    }
}
