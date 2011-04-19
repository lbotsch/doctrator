<?php

    /**
     * Constructor.
     */
    public function __construct()
    {
{% for name, association in config_class.oneToMany %}
        $this->{{ name }} = new \Doctrine\Common\Collections\ArrayCollection();
{% endfor %}
{% for name, association in config_class.manyToMany %}
        $this->{{ name }} = new \Doctrine\Common\Collections\ArrayCollection();
{% endfor %}
    }

{% for name in config_class.columns|merge(config_class.oneToOne)|merge(config_class.oneToMany)|merge(config_class.manyToOne)|merge(config_class.manyToMany)|keys %}
    /**
     * Sets the {{ name }} data.
     *
     * @param mixed ${{ name }} The {{ name }} data.
     */
    public function set{{ name|ucfirst }}(${{ name }})
    {
        $this->{{ name }} = ${{ name }};
    }

    /**
     * Returns the {{ name }} data.
     *
     * @return mixed The {{ name }} data.
     */
    public function get{{ name|ucfirst }}()
    {
        return $this->{{ name }};
    }
{% endfor %}

    /**
     * Set data by name.
     *
     * @param string $name  The data name.
     * @param mixed  $value The value.
     *
     * @throws \InvalidArgumentException If the data does not exists.
     */
    public function set($name, $value)
    {
{% for name in config_class.columns|keys %}
        if ('{{ name }}' == $name) {
            return $this->set{{ name|ucfirst }}($value);
        }
{% endfor %}

        throw new \InvalidArgumentException(sprintf('The data "%s" does not exists.', $name));
    }

    /**
     * Get data by name.
     *
     * @param string $name  The data name.
     *
     * @return mixed The data.
     *
     * @throws \InvalidArgumentException If the data does not exists.
     */
    public function get($name)
    {
{% for name in config_class.columns|keys %}
        if ('{{ name }}' == $name) {
            return $this->get{{ name|ucfirst }}();
        }
{% endfor %}

        throw new \InvalidArgumentException(sprintf('The data "%s" does not exists.', $name));
    }

    /**
     * Import data from an array.
     *
     * @param array $array An array.
     *
     * @return void
     */
    public function fromArray(array $array)
    {
{% for name in config_class.columns|keys %}
        if (isset($array['{{ name }}'])) {
            $this->set{{ name|ucfirst }}($array['{{ name }}']);
        }
{% endfor %}
    }

    /**
     * Export the data to an array.
     *
     * @return array An array with the data.
     */
    public function toArray($withAssociations = true)
    {
        $array = array(
{% for name in config_class.columns|keys %}
            '{{ name }}' => $this->get{{ name|ucfirst }}(),
{% endfor %}
        );

{% for name, association in config_class.oneToOne %}
{% if association.mapped is not defined %}
        if ($withAssociations) {
            $array['{{ name }}'] = $this->get{{ name|ucfirst }}() ? $this->get{{ name|ucfirst }}()->toArray($withAssociations) : null;
        }
{% endif %}
{% endfor %}
{% for name, association in config_class.manyToOne %}
{% if association.mapped is not defined %}
        if ($withAssociations) {
            $array['{{ name }}'] = $this->get{{ name|ucfirst }}() ? $this->get{{ name|ucfirst }}()->toArray($withAssociations) : null;
        }
{% endif %}
{% endfor %}

{% for name, association in config_class.oneToMany %}
{% if association.mapped is not defined %}
        if ($withAssociations) {
            $array['{{ name }}'] = array();
            foreach ($this->get{{ name|ucfirst }}() as $key => $value) {
                $array['{{ name }}'][$key] = $value->toArray(true);
            }
        }
{% endif %}
{% endfor %}
{% for name, association in config_class.manyToMany %}
{% if association.mapped is not defined %}
        if ($withAssociations) {
            $array['{{ name }}'] = array();
            foreach ($this->get{{ name|ucfirst }}() as $key => $value) {
                $array['{{ name }}'][$key] = $value->toArray(true);
            }
        }
{% endif %}
{% endfor %}

        return $array;
    }
