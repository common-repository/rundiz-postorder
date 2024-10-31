<?php


namespace RdPostOrder\App\Libraries;

if (!class_exists('\\RdPostOrder\\App\\Libraries\\CategoryHelper')) {
    class CategoryHelper
    {


        /**
         * Build category hierarchy array.<br>
         * Example usage:
         * <pre>
         * $result = get_categories();
         * $output_tree = $CategoryHelper->buildCategoryHierarchyArray($result);
         * unset($result);
         * </pre>
         * 
         * @link http://stackoverflow.com/questions/13877656/php-hierarchical-array-parents-and-childs Original source code.
         * @param array $items The result of db query.
         * @param integer $parent Parent ID.
         * @return array Return structed children node.
         */
        public function buildCategoryHierarchyArray(array $items, $parent = 0, $depth = 0)
        {
            $output = [];

            foreach ($items as $item) {
                if (isset($item->parent) && $item->parent == $parent) {
                    $item->depth = $depth;
                    $children = $this->buildCategoryHierarchyArray($items, $item->term_id, $depth + 1);

                    if ($children) {
                        $item->children = $children;
                    }

                    $output[] = $item;
                }
            }// endforeach;

            unset($item);
            return $output;
        }// buildCategoryHierarchyArray


        /**
         * Build category with sub in hierarchy structure but flat 2D array.<br>
         * It is required to build hierarchy structure with <code>buildCategoryHierarchyArray()</code> method first.<br>
         * Example usage:
         * <pre>
         * $result = get_categories();
         * $output_tree = $CategoryHelper->buildCategoryHierarchyArray($result);
         * static $output_tree_2d;
         * $output_tree_2d = $CategoryHelper->buildCategoryNestedFlat2DArray($output_tree);
         * unset($output_tree, $result);
         * </pre>
         * 
         * @param array $items The result of db query.
         * @param string $indent_text The indent text.
         * @return array Return formatted hierarchy array into flat 2D array. Example: array(13 => 'category 1', 14 => 'category 2');
         */
        public function buildCategoryNestedFlat2DArray(array $items, $indent_text = '&mdash; ')
        {
            $output = [];

            foreach ($items as $item) {
                $output[$item->term_id] = str_repeat($indent_text, $item->depth) . $item->name;
                if (isset($item->children)) {
                    $output = $output + $this->buildCategoryNestedFlat2DArray($item->children, $indent_text);
                }
            }// endforeach;

            unset($item);
            return $output;
        }// buildCategoryNestedFlat2DArray


    }
}