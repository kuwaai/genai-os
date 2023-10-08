#!/bin/python3

import os
import yaml
import prometheus_client
from prometheus_client.metrics import MetricWrapperBase
from typing import Dict

__file_dir__ = os.path.dirname(os.path.realpath(__file__)) + '/'
config_path = __file_dir__ + './metrics.yaml'
config = {}
with open(config_path, 'r') as f:
    config = yaml.safe_load(f)
    assert config['version'] == 1
    assert config['type'] == 'prometheus'

def get_class_metrics(obj: object) -> Dict[str, MetricWrapperBase]:
    """
    Get metrics with the class name of given object.
    Pass None as the object to get default metrics.
    """

    global config
    class_name = '__main__' if obj == None else type(obj).__name__
    assert class_name in config['classes']

    class_config = config['classes'][class_name]
    return _get_metric_instances(class_config['prefix'], class_config['metrics'])

def _get_metric_instances(prefix:str, template: dict) -> Dict[str, MetricWrapperBase]:
    """
    Instance the metrics from the template.
    """

    metrics = {}
    for name, spec in template.items():
        assert 'type' in spec
        type_class = getattr(prometheus_client, spec.pop('type'))
        description = spec.pop('description', '')
        metrics[name] = type_class(
            '{}_{}'.format(prefix, name),
            description,
            **spec
        )
    return metrics