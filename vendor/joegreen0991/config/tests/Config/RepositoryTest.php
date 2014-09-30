<?php

use Config\Repository;
use Config\FileLoader;

class RepositoryTest extends \PHPUnit_Framework_TestCase {

    use Codeception\Specify;
    
    public function loader()
    {
        return new FileLoader(__DIR__ . '/testfiles');
    }
    
    public function testRepositoryInitialises(){
        
        $this->specify('The repository can return a group', function() {
            
            $repo = new Repository($this->loader());
                                    
            $this->assertTrue($repo->has('app'));
            
            $this->assertFalse($repo->has('database'));
            
            $this->assertTrue($repo->has('app.seconditem'));
            
            $this->assertFalse($repo->has('app.non_existant_item'));
            
        });
        
        $this->specify('The repository can return a value', function() {
            
            $repo = new Repository($this->loader());
            
            $this->assertEquals('second-production', $repo->get('app.seconditem'));
            
        });
        
        $this->specify('All items can be fetched',function(){
            
            $repo = new Repository($this->loader());
            
            $this->assertTrue(is_array($repo->getItems()));
            
        });

    }
    
    public function testSetEnvironment(){
        
        $this->specify('The repository\'s environment can be set at intialisation', function() {
            
            $repo = new Repository($this->loader(),'staging');
                                    
            $this->assertEquals('second-staging', $repo->get('app.seconditem'));
            
        });
        
        $this->specify('The repository\'s environment can be read', function() {
            
            $repo = new Repository($this->loader(),'testing');
            
            $this->assertEquals('testing', $repo->getEnvironment());
            
        });

    }
    
    public function testModifyValue(){
        
        $this->specify('An entire group can be updated', function() {
            
            $repo = new Repository($this->loader());
                                    
            $this->assertSame(include __DIR__ . '/testfiles' . '/app.php', $repo->get('app'));
            
            $new = array(
                'new' => 'value'
            );
            
            $repo->set('app',$new);
            
            $this->assertSame($new, $repo->get('app'));
            
        });
        
        $this->specify('A value in a group can be updated', function() {
            
            $repo = new Repository($this->loader());
                                                
            $this->assertEquals('second-production', $repo->get('app.seconditem'));
            
            $repo->set('app.seconditem','new-value');
            
            $this->assertEquals('new-value', $repo->get('app.seconditem'));
            
        });

    }
    
    public function testDynamicArrayAccessors(){
        
        $this->specify('Set and Get via array access', function() {
            
            $repo = new Repository($this->loader());
                                    
            $this->assertSame(include __DIR__ . '/testfiles' . '/app.php', $repo->get('app'));
            
            $new = array(
                'new' => 'value'
            );
            
            $repo['app'] = $new;
            
            $this->assertSame($new, $repo->get('app'));
            
            $this->assertSame($new, $repo['app']);
            
        });

    }

}