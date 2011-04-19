    /**
     * Set data in the entity.
     *
     * @param string \$name  The data name.
     * @param mixed  \$value The value.
     *
     * @see set()
     */
    public function __set($name, $value)
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
    public function __get($name)
    {
        return $this->get($name);
    }
