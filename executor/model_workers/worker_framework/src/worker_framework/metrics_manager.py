#!/bin/python3

import os
import yaml
import prometheus_client
from prometheus_client.metrics import MetricWrapperBase
from typing import Dict
from worker_framework.config import get_config

__file_dir__ = os.path.dirname(os.path.realpath(__file__)) + '/'

class MetricsManager:
    config_path = __file_dir__ + './metrics.yaml'

    def __init__(self, app_name: str):
        self.name_space = 'worker'
        self.label = {'app_name': app_name}
        
        self.metrics_config = {}
        with open(MetricsManager.config_path, 'r') as f:
            self.metrics_config = yaml.safe_load(f)
            assert self.metrics_config['version'] == 1
            assert self.metrics_config['type'] == 'prometheus'
        

    def get_class_metrics(self, obj: object) -> Dict[str, MetricWrapperBase]:
        """
        Get metrics with the class name of given object.
        Pass None as the object to get default metrics.
        """

        class_name = '__main__' if obj == None else type(obj).__name__
        assert class_name in self.metrics_config['classes']

        class_config = self.metrics_config['classes'][class_name]
        return self._get_metric_instances(
            subsystem = class_config['prefix'],
            template = class_config['metrics']
        )

    def _get_metric_instances(self, subsystem: str, template: dict) -> Dict[str, MetricWrapperBase]:
        """
        Instance the metrics from the template.
        """

        metrics = {}
        for name, spec in template.items():
            assert 'type' in spec
            type_class = getattr(prometheus_client, spec.pop('type'))
            description = spec.pop('description', '')
            metrics[name] = type_class(
                namespace = self.name_space,
                subsystem = subsystem,
                name = name,
                labelnames = tuple(self.label.keys()),
                documentation = description,
                **spec
            ).labels(*list(self.label.values()))
        return metrics

# Public endpoint
def get_metrics_manager(_metrics_manager = MetricsManager(get_config().llm_name)):
    return _metrics_manager
get_class_metrics = get_metrics_manager().get_class_metrics