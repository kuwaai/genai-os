#!/bin/python3

def get_instance_with_prefix(prefix:str, template: dict) -> dict:
    metrics = {}
    for name, spec in template.items():
        assert 'type' in spec
        type_class = spec.pop('type')
        description = spec.pop('description', '')
        metrics[name] = type_class(
            '{}_{}'.format(prefix, name),
            description,
            **spec
        )
    return metrics