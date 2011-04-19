    /**
     * Throws an \LogicException because you cannot check if data exists.
     *
     * @throws \LogicException
     */
    public function offsetExists($offset)
    {
        throw new \LogicException('You cannot check if data exists in the entity.');
    }

    /**
     * Set data in the entity.
     *
     * @param string \$name  The data name.
     * @param mixed  \$value The value.
     *
     * @see set()
     */
    public function offsetSet($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Returns data of the entity.
     *
     * @param string \$name The data name.
     *
     * @return mixed Some data.
     *
     * @see get()
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Throws a \LogicException because you cannot unset data in the entity.
     *
     * @throws \LogicException
     */
    public function offsetUnset($name)
    {
        throw new \LogicException('You cannot unset data in the entity.');
    }
