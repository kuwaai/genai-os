#!/bin/python

import fileinput
import math
import operator
import ast
import sys

class MathSyntaxError(Exception):
    def __init__(self, message:str, node:ast.AST):
        super().__init__(message)
        width = 1
        self.message = message
        self.node = node
        self.offset = self.get_offset()
        self.end_offset = self.offset + width
    def get_offset(self):
        if isinstance(self.node, ast.BinOp):
            return self.node.right.col_offset
        else:
            return self.node.col_offset+1
    def __str__(self):
        return self.message

def safe_eval(s):
    """
    Evaluate simple arithmetic expressions safely.
    Ref: https://stackoverflow.com/a/68732605
    """
    def checkfunc(node, x, *args):
        supported_func = [x for x in dir(math) if "__" not in x]
        if x not in supported_func:
            raise MathSyntaxError(f"invalid function \"{x}\". Use: {', '.join(supported_func)}", node)
        fun = getattr(math, x)
        return fun(*args)

    def checkconst(node, x):
        supported_const = [k for k, v in vars(math).items() if not k.startswith('_') and not callable(v)]
        if x not in supported_const:
            raise MathSyntaxError(f"invalid constant \"{x}\". Use: {', '.join(supported_const)}", node)
        value = getattr(math, x)
        return value

    bin_ops = {
        ast.Add: operator.add,
        ast.Sub: operator.sub,
        ast.Mult: operator.mul,
        ast.Div: operator.truediv,
        ast.Mod: operator.mod,
        ast.Pow: operator.pow,
        ast.BitXor: operator.pow,
        ast.BinOp: ast.BinOp,
    }

    un_ops = {
        ast.USub: operator.neg,
        ast.UAdd: operator.pos,
        ast.UnaryOp: ast.UnaryOp,
    }

    tree = ast.parse(s, mode="eval")

    def _eval(node):
        if isinstance(node, ast.Expression):
            return _eval(node.body)
        if isinstance(node, ast.Constant):
            return node.value
        if isinstance(node, ast.BinOp):
            left  = _eval(node.left)
            right = _eval(node.right)
            if type(node.op) not in bin_ops:
                raise MathSyntaxError(f"invalid operator \"{type(node).__name__}\"", node)
            return bin_ops[type(node.op)](left, right)
        if isinstance(node, ast.UnaryOp):
            operand = _eval(node.operand)
            if type(node.op) not in un_ops:
                raise MathSyntaxError(f"invalid operator \"{type(node).__name__}\"", node)
            return un_ops[type(node.op)](operand)
        if isinstance(node, ast.Call):
            args = [_eval(x) for x in node.args]
            return checkfunc(node, node.func.id, *args)
        if isinstance(node, ast.Name):
            return checkconst(node, node.id)
        raise MathSyntaxError(f"invalid syntax \"{type(node).__name__}\"", node)

    return _eval(tree)

def main():
    for line in fileinput.input():
        try:
            line = line.strip()
            result = safe_eval(line)
            print(str(result))
        except (MathSyntaxError, SyntaxError) as e:
            print(line)
            print(f"{' '*(e.offset-1)}{'^'*(e.end_offset-e.offset)}")
            print(f"{type(e).__name__}: {e.args[0]}")
        except Exception as e:
            print(f"{type(e).__name__}: {e.args[0]}")


if __name__ == "__main__":
    sys.tracebacklimit = -1
    main()