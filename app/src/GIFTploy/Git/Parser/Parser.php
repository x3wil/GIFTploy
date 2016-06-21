<?php

namespace GIFTploy\Git\Parser;

/**
 * Parser interface
 *
 * @author Patrik Chotěnovský
 */
interface Parser
{

    /**
     * Parse raw git output.
     *
     * @param string $rawLog
     * @return \Generator
     */
    public function parse($rawLog);

}
