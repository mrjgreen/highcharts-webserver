<?php
use Config\FileLoader;

class FileLoaderTest extends \PHPUnit_Framework_TestCase {

    use Codeception\Specify;
    
    public function sampleFiles(){
        return __DIR__ . '/testfiles';
    }
    
    public function testGroupExistsMethod(){
        $this->specify('Test loader can check if a group exists', function() {
            
            $dir = $this->sampleFiles();
            
            $loader = new FileLoader($dir);
            
            $this->assertTrue($loader->load('app'));
            
            $loader->exists('app'); // Check cache is used - check code coverage for this
            
            $this->assertFalse($loader->load('no_environment'));
            
        });
    }

    
    public function testFileLoadCanLoadFiles(){
        
        $this->specify('Test basic file load', function() {
            
            $dir = $this->sampleFiles();
            
            $loader = new FileLoader($dir);
            
            $loaded = $loader->load(null,'app');
            
            $this->assertSame(include $dir . '/app.php', $loaded);
            
        });
        
        $this->specify('Test load file that doesn\'t exist', function() {
            
            $dir = $this->sampleFiles();
            
            $loader = new FileLoader($dir);
            
            $loaded = $loader->load(null,'database');
            
            $this->assertEquals(null, $loaded);
            
        });

        $this->specify('Test file load with merged environments', function() {
            
            $dir = $this->sampleFiles();
            
            $loader = new FileLoader($dir);
            
            $loaded = $loader->load('staging','app');
            
            $merged = array_replace_recursive(
                    include $dir . '/app.php',
                    include $dir . '/staging/app.php');
            
            $this->assertSame($merged, $loaded);
            
        });

    }
        

}