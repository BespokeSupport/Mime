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

    public function testMimeAtom()
    {
        $mime = 'application/atom+xml';
        $extension = 'xml';
        $mimes = new \BespokeSupport\Mime\FileMimes();
        $mimeReturn = $mimes->getMimeFromExtension($extension);
        $this->assertEquals($mime, $mimeReturn);
    }

    public function testMimeDocFail()
    {
        $mime = 'application/atom+xml';
        $extension = 'docx';
        $mimes = new \BespokeSupport\Mime\FileMimes();
        $mimeReturn = $mimes->getMimeFromExtension($extension);
        $this->assertNotNull($mimeReturn);
        $this->assertNotEquals($mimeReturn,$mime);
    }

    public function testExtensionFail()
    {
        $mime = 'application/foobar';
        $extension = 'FOOBARBAR';
        $mimes = new \BespokeSupport\Mime\FileMimes();
        $mimeReturn = $mimes->getMimeFromExtension($extension);
        $this->assertNull($mimeReturn);
        $this->assertNotEquals($mimeReturn,$mime);
    }


}
