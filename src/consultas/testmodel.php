<?php
// -- Only one caveat : The results must be ordered so that an item's parent will be processed first.
// -- Simulate a DB result
$results = array();
$results[] = array('id' => 'a', 'parent' => '',  'name' => 'Johnny');
$results[] = array('id' => 'b', 'parent' => 'a', 'name' => 'Bobby');
$results[] = array('id' => 'c', 'parent' => 'b', 'name' => 'Marky');
$results[] = array('id' => 'd', 'parent' => 'a', 'name' => 'Ricky');
$results[] = array('id' => 'e', 'parent' => '',  'name' => 'Timmy');
$results[] = array('id' => 'g', 'parent' => 'f', 'name' => 'Mary'); // -- Child is here before parent to demonstrate the second pass working
$results[] = array('id' => 'f', 'parent' => 'e', 'name' => 'Tommy');
$results[] = array('id' => 'h', 'parent' => 'b', 'name' => 'Donny');
function convertToHierarchy($results, $idField='id', $parentIdField='parent', $childrenField='children') {
	$hierarchy = array(); // -- Stores the final data
	$itemReferences = array(); // -- temporary array, storing references to all items in a single-dimention
	foreach ( $results as $item ) {
		$id       = $item[$idField];
		$parentId = $item[$parentIdField];
		if (isset($itemReferences[$parentId])) { // parent exists
			$itemReferences[$parentId][$childrenField][$id] = $item; // assign item to parent
			$itemReferences[$id] =& $itemReferences[$parentId][$childrenField][$id]; // reference parent's item in single-dimentional array
		} elseif (!$parentId || !isset($hierarchy[$parentId])) { // -- parent Id empty or does not exist. Add it to the root
			$hierarchy[$id] = $item;
			$itemReferences[$id] =& $hierarchy[$id];
		}
	}
	unset($results, $item, $id, $parentId);
	// -- Run through the root one more time. If any child got added before it's parent, fix it.
	foreach ( $hierarchy as $id => &$item ) {
		$parentId = $item[$parentIdField];
		if ( isset($itemReferences[$parentId] ) ) { // -- parent DOES exist
			$itemReferences[$parentId][$childrenField][$id] = $item; // -- assign it to the parent's list of children
			unset($hierarchy[$id]); // -- remove it from the root of the hierarchy
		}
	}
	unset($itemReferences, $id, $item, $parentId);
	return $hierarchy;
}
var_dump(json_encode(convertToHierarchy($results)));