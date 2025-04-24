<style>
    table {
        border-collapse: separate;
        border-spacing: 10px 50px;
    }

    th {
        border: 5px solid green;
    }

    td {
        border: 5px solid blue;
    }

    td:last-child {
        border-color: orange;
    }
</style>

<table>
    <tr>
        <th>1</th>
        <th>2</th>
        <th>3</th>
    </tr>
    <tr>
        <td>4</td>
        <td>5</td>
        <td>6</td>
    </tr>

</table>

<input type="text" value="!@###" readonly>
<label id="size">Size</label>

<input type="range" id="size" name="size" step="10" min="0" max="100" value="20">