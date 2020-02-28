export default function initTodo(todo) {
    return {
        todo: todo.todo,
        show_destroy: false,
        edit: false,
        editing(element) {
            this.edit = true;
            setTimeout(function() {
                const input = element;
                input.focus();
                const { value } = input;
                input.value = ''; // reset value to make cursor at the end of the text

                input.value = value;
            }, 100);
        },
        leaveEditing() {
            this.edit = false;
            this.todo = todo.todo;
        }
    };
}
