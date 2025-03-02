<?php 
    class Test extends Controller{
        public function index(){
            echo "<h1>Test Controller</h1>";
            echo "<p>The routing is working if you can see this message.</p>";
            
            echo "<h2>URL Debug Information</h2>";
            echo "<pre>";
            echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
            echo "</pre>";
            
            // Don't use the view method to avoid template wrapping
        }
    }