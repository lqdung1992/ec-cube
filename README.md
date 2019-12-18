## Override EC core by customize/plugin entity (trait)

Override core by trait attributes:
- Field properties (almost attributes of field)
- Association properties (only some attributes as defined by doctrine)

ex: change tag name from `255` (default of ec4) to `200` by trait
- Tag entity (Eccube\Entity\Tag)
```
/**
 * @var string
 *
 * @ORM\Column(name="name", type="string", length=255)
 */
protected $name;
```

- TagTrait (Customize\Entity\TagTrait)
```
/**
 * @var string
 *
 * @ORM\Column(name="name", type="string", length=200)
 */
protected $name;
```

Run `php bin/console d:s:u --dump-sql -f`, and see the result (*):

`ALTER TABLE dtb_tag ALTER name TYPE VARCHAR(200);`


(*) note: Please run generate proxy first.

## Test
- Tested `4.0.2`, `4.0.3`

## Todo:
- Make a free plugin ^!^