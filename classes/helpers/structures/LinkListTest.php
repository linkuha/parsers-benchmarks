<?php


class LinkListTest extends PHPUnit_Framework_TestCase
{
    public function testLinkList()
    {
        
        $totalNodes = 100;
        
        $theList = new LinkList();
    
        for($i=1; $i <= $totalNodes; $i++)
        {
            $theList->insertLast($i);
        }
        
        $this->assertEquals($totalNodes, $theList->totalNodes());
        
        for($i=1; $i <= $totalNodes; $i++)
        {
            $theList->insertFirst($i);
        }
	
        $totalNodes = $totalNodes * 2;
        $this->assertEquals($totalNodes, $theList->totalNodes());
        
        $theList->reverseList();
        $this->assertEquals($totalNodes, $theList->totalNodes());
        
        $theList->deleteFirstNode();
        $this->assertEquals($totalNodes - 1, $theList->totalNodes());
        
        $theList->deleteLastNode();
        $this->assertEquals($totalNodes - 2, $theList->totalNodes());
        
        /* Delete node which has a value of '5' */
        $theList->deleteNode(5);
        $this->assertEquals($totalNodes - 3, $theList->totalNodes());
        
        /* Insert a node at the end of the list with a value of '22' */
        $theList->insertLast(22);
        $this->assertEquals($totalNodes - 2, $theList->totalNodes());
        
        /* Find a node with a value of '25' (is in the list) */
        $found = $theList->find(25);
        $this->assertEquals(25, $found->data);

        /* Find a node with a value of '125' (is not in the list) */
        $found = $theList->find(125);
        $this->assertNull($found);
        
        /* Return the data stored in the node at position '50' */
        $data = $theList->readNode(50);
        $this->assertEquals(50, $data);
        
        /* Return the data stored in the node at position '450' */
        $data = $theList->readNode(450);
        $this->assertNull($data);
    }
}

?>