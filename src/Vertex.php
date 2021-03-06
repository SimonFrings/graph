<?php

namespace Graphp\Graph;

use Graphp\Graph\Exception\BadMethodCallException;
use Graphp\Graph\Set\Edges;
use Graphp\Graph\Set\EdgesAggregate;
use Graphp\Graph\Set\Vertices;

class Vertex extends Entity implements EdgesAggregate
{
    /**
     * @var Edge[]
     */
    private $edges = array();

    /**
     * @var Graph
     */
    private $graph;

    /**
     * Create a new Vertex
     *
     * @param Graph $graph      graph to be added to
     * @param array $attributes
     * @see Graph::createVertex() to create new vertices
     */
    public function __construct(Graph $graph, array $attributes = array())
    {
        $this->graph = $graph;
        $this->attributes = $attributes;

        $graph->addVertex($this);
    }

    /**
     * get graph this vertex is attached to
     *
     * @return Graph
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * add the given edge to list of connected edges (MUST NOT be called manually)
     *
     * @param  Edge                     $edge
     * @return void
     * @internal
     * @see Graph::createEdgeUndirected() instead!
     */
    public function addEdge(Edge $edge)
    {
        $this->edges[] = $edge;
    }

    /**
     * check whether this vertex has a direct edge to given $vertex
     *
     * @param  Vertex  $vertex
     * @return bool
     * @uses Edge::hasVertexTarget()
     */
    public function hasEdgeTo(Vertex $vertex)
    {
        $that = $this;

        return $this->getEdges()->hasEdgeMatch(function (Edge $edge) use ($that, $vertex) {
            return $edge->isConnection($that, $vertex);
        });
    }

    /**
     * check whether the given vertex has a direct edge to THIS vertex
     *
     * @param  Vertex  $vertex
     * @return bool
     * @uses Vertex::hasEdgeTo()
     */
    public function hasEdgeFrom(Vertex $vertex)
    {
        return $vertex->hasEdgeTo($this);
    }

    /**
     * get set of ALL Edges attached to this vertex
     *
     * @return Edges
     */
    public function getEdges()
    {
        return new Edges($this->edges);
    }

    /**
     * get set of all outgoing Edges attached to this vertex
     *
     * @return Edges
     */
    public function getEdgesOut()
    {
        $that = $this;
        $prev = null;

        return $this->getEdges()->getEdgesMatch(function (Edge $edge) use ($that, &$prev) {
            $ret = $edge->hasVertexStart($that);

            // skip duplicate directed loop edges
            if ($edge === $prev && $edge instanceof EdgeDirected) {
                $ret = false;
            }
            $prev = $edge;

            return $ret;
        });
    }

    /**
     * get set of all ingoing Edges attached to this vertex
     *
     * @return Edges
     */
    public function getEdgesIn()
    {
        $that = $this;
        $prev = null;

        return $this->getEdges()->getEdgesMatch(function (Edge $edge) use ($that, &$prev) {
            $ret = $edge->hasVertexTarget($that);

            // skip duplicate directed loop edges
            if ($edge === $prev && $edge instanceof EdgeDirected) {
                $ret = false;
            }
            $prev = $edge;

            return $ret;
        });
    }

    /**
     * get set of Edges FROM this vertex TO the given vertex
     *
     * @param  Vertex $vertex
     * @return Edges
     * @uses Edge::hasVertexTarget()
     */
    public function getEdgesTo(Vertex $vertex)
    {
        $that = $this;

        return $this->getEdges()->getEdgesMatch(function (Edge $edge) use ($that, $vertex) {
            return $edge->isConnection($that, $vertex);
        });
    }

    /**
     * get set of Edges FROM the given vertex TO this vertex
     *
     * @param  Vertex $vertex
     * @return Edges
     * @uses Vertex::getEdgesTo()
     */
    public function getEdgesFrom(Vertex $vertex)
    {
        return $vertex->getEdgesTo($this);
    }

    /**
     * get set of adjacent Vertices of this vertex (edge FROM or TO this vertex)
     *
     * If there are multiple parallel edges between the same Vertex, it will be
     * returned several times in the resulting Set of Vertices. If you only
     * want unique Vertex instances, use `getVerticesDistinct()`.
     *
     * @return Vertices
     * @uses Edge::hasVertexStart()
     * @uses Edge::getVerticesToFrom()
     * @uses Edge::getVerticesFromTo()
     */
    public function getVerticesEdge()
    {
        $ret = array();
        foreach ($this->edges as $edge) {
            if ($edge->hasVertexStart($this)) {
                $ret []= $edge->getVertexToFrom($this);
            } else {
                $ret []= $edge->getVertexFromTo($this);
            }
        }

        return new Vertices($ret);
    }

    /**
     * get set of all Vertices this vertex has an edge to
     *
     * If there are multiple parallel edges to the same Vertex, it will be
     * returned several times in the resulting Set of Vertices. If you only
     * want unique Vertex instances, use `getVerticesDistinct()`.
     *
     * @return Vertices
     * @uses Vertex::getEdgesOut()
     * @uses Edge::getVerticesToFrom()
     */
    public function getVerticesEdgeTo()
    {
        $ret = array();
        foreach ($this->getEdgesOut() as $edge) {
            $ret []= $edge->getVertexToFrom($this);
        }

        return new Vertices($ret);
    }

    /**
     * get set of all Vertices that have an edge TO this vertex
     *
     * If there are multiple parallel edges from the same Vertex, it will be
     * returned several times in the resulting Set of Vertices. If you only
     * want unique Vertex instances, use `getVerticesDistinct()`.
     *
     * @return Vertices
     * @uses Vertex::getEdgesIn()
     * @uses Edge::getVerticesFromTo()
     */
    public function getVerticesEdgeFrom()
    {
        $ret = array();
        foreach ($this->getEdgesIn() as $edge) {
            $ret []= $edge->getVertexFromTo($this);
        }

        return new Vertices($ret);
    }

    /**
     * do NOT allow cloning of objects
     *
     * @throws BadMethodCallException
     */
    private function __clone()
    {
        // @codeCoverageIgnoreStart
        throw new BadMethodCallException();
        // @codeCoverageIgnoreEnd
    }
}
