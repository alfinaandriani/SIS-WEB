document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const parentFields = document.getElementById('parent_fields');
    const doctorFields = document.getElementById('doctor_fields');

    roleSelect.addEventListener('change', function() {
        if (roleSelect.value === 'Orang Tua') {
            parentFields.style.display = 'block';
            doctorFields.style.display = 'none';
        } else if (roleSelect.value === 'Dokter') {
            parentFields.style.display = 'none';
            doctorFields.style.display = 'block';
        }
    });
    function showAddChildForm() {
        document.getElementById('addChildForm').style.display = 'block';
    }
});
