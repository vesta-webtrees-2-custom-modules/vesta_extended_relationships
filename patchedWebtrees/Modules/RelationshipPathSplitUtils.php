<?php

namespace Cissee\WebtreesExt\Modules;

class RelationshipPathSplitUtils {
  
  /**
   * using this predicate with a high priority within RelationshipPathSplitPredicate
   * ensures that common-ancestor-based subpath are evaluated prioritized 
   * (which is usually a good idea because they often have RelDef's for all cases, 
   * or at least a small number of required splits)
   * 
   * 
   * @param $split
   * @return bool
   */
  public static function isNextToSpouse(RelationshipPathSplit $split): bool {
    $previous = $split->head()->last();
    if ($previous === null) {
      return false;
    }
    $next = $split->tail()->first();
    if ($next === null) {
      return false;
    }
    return $previous->relIsAnySpouse() || $next->relIsAnySpouse();
  }
    
  /**
   * using this predicate with a high priority within RelationshipPathSplitPredicate
   * helps to manage common-ancestor based relationships without a sibling rel
   * in case there is no explicit RelDef for it
   * (often very useful for performance improvement, even in cases where the resulting name isn't affected)
   * 
   * @param $split
   * @return bool
   */
  public static function isAscentToDescent(RelationshipPathSplit $split): bool {
    $previous = $split->head()->last();
    if ($previous === null) {
      return false;
    }
    $next = $split->tail()->first();
    if ($next === null) {
      return false;
    }
    return $previous->relIsAnyParent() && $next->relIsAnyChild();
  }
  
  /**
   * using this predicate with a high priority within RelationshipPathSplitPredicate
   * helps to managed great-x-uncle/aunt/nephew/niece relationships
   * in case
   * a) there is no explicit RelDef for it
   * b) the split 'sister' + 'great-grandson' is preferred to a split around the ancestor/descendant relations such as
   * 'niece' +'grandson'
   * 
   * (but see isNextToTerminalSibling if this conflicts with a simultaneous use of isWithinAscent/isWithinDescent)
   * 
   * @param $split
   * @return bool
   */
  public static function isNextToSibling(RelationshipPathSplit $split): bool {
    $previous = $split->head()->last();
    if ($previous === null) {
      return false;
    }
    $next = $split->tail()->first();
    if ($next === null) {
      return false;
    }
    return $previous->relIsAnySibling() || $next->relIsAnySibling();
  }
  
  public static function isNextToTerminalSibling(RelationshipPathSplit $split): bool {
    $previous = $split->head()->last();
    if ($previous === null) {
      return false;
    }
    $next = $split->tail()->first();
    if ($next === null) {
      return false;
    }
    return 
      (($split->head()->size() === 1) && $previous->relIsAnySibling()) 
      || 
      (($split->tail()->size() === 1) && $next->relIsAnySibling());
  }
  
  /**
   * using this predicate with a high priority within RelationshipPathSplitPredicate
   * helps to managed 'skewed' cousin relationships such as 'second cousin twice removed ascending'
   * in case
   * a) there is no explicit RelDef for it
   * b) the split 'grandfather' + 'second cousin' is preferred to a split around the sibling relation such as
   * 'great-x3-grandfather' + 'great-x2-nephew'
   * 
   * @param $split
   * @return bool
   */
  public static function isWithinAscent(RelationshipPathSplit $split): bool {
    $previous = $split->head()->last();
    if ($previous === null) {
      return false;
    }
    $next = $split->tail()->first();
    if ($next === null) {
      return false;
    }
    return $previous->relIsAnyParent() && $next->relIsAnyParent();
  }
  
  /**
   * use case is symmetric to isWithinAscent
   * 
   * @param $split
   * @return bool
   */
  public static function isWithinDescent(RelationshipPathSplit $split): bool {
    $previous = $split->head()->last();
    if ($previous === null) {
      return false;
    }
    $next = $split->tail()->first();
    if ($next === null) {
      return false;
    }
    return $previous->relIsAnyChild() && $next->relIsAnyChild();
  }
}
