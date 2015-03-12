<?php

class MimeTests extends PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $mimes = new \BespokeSupport\Mime\FileMimes();

        $this->assertGreaterThan(0, count($mimes->getMimeNames()));
        $this->assertGreaterThan(0, count($mimes->getMimes()));
    }

    public function testBlankFunctions()
    {
        $mimes = new \BespokeSupport\Mime\FileMimes();

        $ext = $mimes->getExtensionFromMime();
        $this->assertEquals($ext, '');

        $name = $mimes->getNameFromMime();
        $this->assertEquals($name, '');
    }

    public function testExtension()
    {
        $mime = 'application/java-archive';
        $mimes = new \BespokeSupport\Mime\FileMimes();
        $ext = $mimes->getExtensionFromMime($mime);
        $this->assertEquals($ext, 'jar');
    }

    public function testName()
    {
        $mime = 'application/java-archive';
        $mimes = new \BespokeSupport\Mime\FileMimes();
        $ext = $mimes->getNameFromMime($mime);
        $this->assertEquals($ext, 'Java Archive');
    }

    public function testExtensionAtom()
    {
        $mime = 'application/atom+xml';
        $mimes = new \BespokeSupport\Mime\FileMimes();
        $ext = $mimes->getExtensionFromMime($mime);
        $this->assertEquals($ext, 'xml');
    }

    public function testNameAtom()
    {
        $mime = 'application/atom+xml';
        $mimes = new \BespokeSupport\Mime\FileMimes();
        $ext = $mimes->getNameFromMime($mime);
        $this->assertEquals($ext, 'Atom Syndication Format');
    }







}
