<?php
/*
This project is Licenced under The MIT License (MIT)

Copyright (c) 2014 Christopher Seufert

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

 */

namespace Seufert\Hamle {
  use Iterator;

  /**
   * HAMLE Model Interface, all models used in the template must implmenent this
   *
   * @author Chris
   */
  interface Model extends Iterator
  {
    /**
     * hamleGet Must be implemented to get a variable using key
     *
     * @param string $key String of key name to retreive
     */
    function hamleGet(string $key): mixed;

    /**
     * Retreive an iterable result of relatives to the current object
     * @param int $rel Relation to this object hamle::REL_CHILD, etc
     * @param array $typeTags Array of types to search containing tags
     *                                    eg([photo->[landscape,wide]])
     * @param array $sort Array of SORT fields(key) with DIRECTION(value)
     * @param int $limit Limit of rows to return
     * @param int $offset Offset Number of rows to offset results by
     * @param int $grouptype Type of group
     * @return Model Return object must implmement hamleModel interface
     */
    function hamleRel(
      int $rel,
      array $typeTags,
      array $sort = [],
      int $limit = 0,
      int $offset = 0,
      int $grouptype = 1
    ): Model;
  }
}
