#!/bin/python

import fileinput
import math
import operator
import ast

def safe_eval(s):
    """
    Evaluate simple arithmetic expressions safely.
    Ref: https://stackoverflow.com/a/68732605
    """
    def checkmath(x, *args):
        if x not in [x for x in dir(math) if "__" not in x]:
            raise SyntaxError(f"Unknown func {x}()")
        fun = getattr(math, x)
        return fun(*args)

    def checkconst(x):
        const = [k for k, v in vars(math).items() if not k.startswith('_') and not callable(v)]
        if x not in const:
            raise SyntaxError(f"Unknown constant {x}")
        value = getattr(math, x)
        return value

    bin_ops = {
        ast.Add: operator.add,
        ast.Sub: operator.sub,
        ast.Mult: operator.mul,
        ast.Div: operator.truediv,
        ast.Mod: operator.mod,
        ast.Pow: operator.pow,
        ast.Call: checkmath,
        ast.BinOp: ast.BinOp,
    }

    un_ops = {
        ast.USub: operator.neg,
        ast.UAdd: operator.pos,
        ast.UnaryOp: ast.UnaryOp,
        ast.Name: checkconst,
    }

    ops = tuple(bin_ops) + tuple(un_ops)

    tree = ast.parse(s, mode="eval")

    def _eval(node):
        if isinstance(node, ast.Expression):
            return _eval(node.body)
        if isinstance(node, ast.Constant):
            return node.value
        if isinstance(node, ast.BinOp):
            left = _eval(node.left) if isinstance(node.left, ops) else node.left.value
            if isinstance(node.right, ops):
                right = _eval(node.right)
            else:
                right = node.right.value
            return bin_ops[type(node.op)](left, right)
        if isinstance(node, ast.UnaryOp):
            if isinstance(node.operand, ops):
                operand = _eval(node.operand)
            else:
                operand = node.operand.value
            return un_ops[type(node.op)](operand)
        if isinstance(node, ast.Call):
            args = [_eval(x) for x in node.args]
            return checkmath(node.func.id, *args)
        if isinstance(node, ast.Name):
            return checkconst(node.id)
        raise SyntaxError(f"Bad syntax, {type(node)}")

    return _eval(tree)

def main():
    for line in fileinput.input():
        result = safe_eval(line)
        print(str(result))

if __name__ == "__main__":
  main()