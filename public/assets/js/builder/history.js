window.NSBuilder = window.NSBuilder || {};

NSBuilder.History = (function () {
  const stack = [];
  let index = -1;
  const max = 50;

  function clone(doc) {
    return JSON.parse(JSON.stringify(doc));
  }

  function push(doc) {
    stack.splice(index + 1);
    stack.push(clone(doc));
    if (stack.length > max) stack.shift();
    index = stack.length - 1;
  }

  function undo(current) {
    if (index <= 0) return null;
    index -= 1;
    return clone(stack[index]);
  }

  function redo() {
    if (index >= stack.length - 1) return null;
    index += 1;
    return clone(stack[index]);
  }

  function canUndo() { return index > 0; }
  function canRedo() { return index < stack.length - 1; }

  return { push, undo, redo, canUndo, canRedo, clone };
})();
