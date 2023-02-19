<?php

namespace Cissee\Webtrees\Module\ExtendedRelationships;

/**
 * Class OptimizedDijkstra - Use Dijkstra's algorithm to calculate the shortest path
 * through a weighted, directed graph.
 */
class OptimizedDijkstra {

    /** @var integer[][] The graph, where $graph[node1][node2]=cost */
    protected $graph;

    /** @var integer[] Distances from the source node to each other node */
    protected $distance;

    /** @var integer Distances from the source node to the target node */
    protected $distanceToTarget;

    /** @var string[][] The previous node(s) in the path to the current node */
    protected $previous;

    /** @var integer[] Nodes which have yet to be processed */
    protected $queue;

    /**
     * @param integer[][] $graph
     */
    public function __construct($graph) {
        $this->graph = $graph;
    }

    /**
     * Process the next (i.e. closest) entry in the queue
     *
     * @param string[] $exclude A list of nodes to exclude - for calculating next-shortest paths.
     * @param string $target abort when min distance(s) to target are found (otherwise compute shortest paths to all nodes)
     * @return void
     */
    protected function processNextNodeInQueue(
        array $exclude, 
        $target = null) {
        
        // Process the closest vertex
        $closest = array_search(min($this->queue), $this->queue);

        if (($target !== null) && $this->distanceToTarget < $this->distance[$closest]) {
            //we're done, all further nodes in queue have (or would have) greater distance!
            $this->queue = array();
            return;
        }

        if (!empty($this->graph[$closest]) && !in_array($closest, $exclude)) {
            foreach ($this->graph[$closest] as $neighbor => $cost) {
                if (isset($this->distance[$neighbor])) {
                    if ($this->distance[$closest] + $cost < $this->distance[$neighbor]) {
                        // A shorter path was found
                        $this->distance[$neighbor] = $this->distance[$closest] + $cost;
                        $this->previous[$neighbor] = array($closest);
                        $this->queue[$neighbor] = $this->distance[$neighbor];

                        if ($neighbor === $target) {
                            $this->distanceToTarget = $this->distance[$neighbor];
                        }
                    } elseif ($this->distance[$closest] + $cost === $this->distance[$neighbor]) {
                        // An equally short path was found
                        $this->previous[$neighbor] []= $closest;

                        //this is unnecessary - there is no need to add this node again!
                        //it may even have been processed already (in case of edges with weight 0)
                        //$this->queue[$neighbor] = $this->distance[$neighbor];
                    }
                }
            }
        }
        unset($this->queue[$closest]);
    }

    /**
     * Extract all the paths from $source to $target as arrays of nodes.
     *
     * @param string $target The starting node (working backwards)
     *
     * @return string[][] One or more shortest paths, each represented by a list of nodes
     */
    protected function extractPaths($target) {
        $paths = array(array($target));

        for ($key = 0; isset($paths[$key]); ++$key) {
            $path = $paths[$key];

            if (!empty($this->previous[$path[0]])) {
                foreach ($this->previous[$path[0]] as $previous) {
                    $copy = $path;
                    array_unshift($copy, $previous);
                    $paths[] = $copy;
                }
                unset($paths[$key]);
            }
        }

        return array_values($paths);
    }

    /**
     * Calculate the shortest path through a a graph, from $source to $target.
     *
     * @param string   $source  The starting node
     * @param string   $target  The ending node
     * @param string[] $exclude A list of nodes to exclude - for calculating next-shortest paths.
     *
     * @return string[][] Zero or more shortest paths, each represented by a list of nodes
     */
    public function shortestPaths(
        $source, 
        $target, 
        array $exclude = array()) {
        
        // The shortest distance to all nodes starts with infinity...
        $this->distance = array_fill_keys(array_keys($this->graph), INF);
        // ...except the start node
        $this->distance[$source] = 0;

        // The previously visited nodes
        $this->previous = array_fill_keys(array_keys($this->graph), array());

        $this->distanceToTarget = INF;

        // Process all nodes in order
        $this->queue = array($source => 0);
        while (!empty($this->queue)) {
            $this->processNextNodeInQueue($exclude, $target);
        }

        if ($source === $target) {
            // A null path
            return array(array($source));
        } elseif (empty($this->previous[$target])) {
            // No path between $source and $target
            return array();
        } else {
            // One or more paths were found between $source and $target
            return $this->extractPaths($target);
        }
    }

    public function calculateTiebreaker(
        array $path, 
        DijkstraTiebreakerFunction $f): int {
        
        $previous = null;
        foreach ($path as $node) {
            if ($previous !== null) {
                $weight = $this->graph[$previous][$node];
                $f->next($weight);
            }
            
            $previous = $node;
        }
        
        return $f->conclude();
    }
}
