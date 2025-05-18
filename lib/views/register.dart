import 'package:flutter/material.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final ciController = TextEditingController();
  final nameController = TextEditingController();
  final lastNameController = TextEditingController();
  final birthDateController = TextEditingController();
  final expeditionPlaceController = TextEditingController();
  final expeditionDateController = TextEditingController();
  final phoneController = TextEditingController();
  final emailController = TextEditingController();
  final accountNumberController = TextEditingController();
  final donationAmountController = TextEditingController();

  String? ciStatus;
  String? selectedBank;
  String? accountType;
  DateTime? birthDate;
  DateTime? expeditionDate;

  final List<String> ciStatusOptions = ['Active', 'Expired'];
  final List<String> banks = [
    'Banco Pichincha',
    'Produbanco',
    'Banco Pacifico'
  ];
  final List<String> accountTypes = ['Savings', 'Current', 'Other'];

  Future<void> _pickDate(
      BuildContext context, TextEditingController controller, DateTime? initialDate) async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: initialDate ?? now,
      firstDate: DateTime(1900),
      lastDate: now,
    );
    if (picked != null) {
      controller.text = "${picked.year}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}";
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Register'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. C.I label
              const Text('C.I', style: TextStyle(fontWeight: FontWeight.bold)),
              // 2. C.I number input text
              TextFormField(
                controller: ciController,
                decoration: const InputDecoration(
                  hintText: 'Enter your C.I number',
                  border: OutlineInputBorder(),
                ),
                keyboardType: TextInputType.number,
                maxLength: 10,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'C.I Document is required';
                  }
                  if (!RegExp(r'^\d{10}$').hasMatch(value)) {
                    return 'C.I Document must be exactly 10 digits';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 8),
              // 3. Scan C.I QR button
              ElevatedButton.icon(
                icon: const Icon(Icons.qr_code_scanner),
                label: const Text('Scan C.I QR'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFED6F1D),
                  foregroundColor: Colors.white,
                ),
                onPressed: () {
                  // TODO: Implement QR scan logic
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('QR scan not implemented')),
                  );
                },
              ),
              const SizedBox(height: 8),
              // 4. Label for QR scan info
              const Text(
                'If you scan your QR the following data will be completed automatically.',
                style: TextStyle(color: Colors.grey),
              ),
              const SizedBox(height: 16),
              // 5. Name label
              const Text('Name', style: TextStyle(fontWeight: FontWeight.bold)),
              // 6. Name input text
              TextFormField(
                controller: nameController,
                decoration: const InputDecoration(
                  hintText: 'Enter your name',
                  border: OutlineInputBorder(),
                ),
                validator: (value) =>
                    value == null || value.trim().isEmpty ? 'Name is required' : null,
              ),
              const SizedBox(height: 16),
              // 7. Last name label
              const Text('Last Name', style: TextStyle(fontWeight: FontWeight.bold)),
              // 8. Last name input text
              TextFormField(
                controller: lastNameController,
                decoration: const InputDecoration(
                  hintText: 'Enter your last name',
                  border: OutlineInputBorder(),
                ),
                validator: (value) =>
                    value == null || value.trim().isEmpty ? 'Last name is required' : null,
              ),
              const SizedBox(height: 16),
              // 9. Birth-date label
              const Text('Birth-date', style: TextStyle(fontWeight: FontWeight.bold)),
              // 10. Birth-date input text
              TextFormField(
                controller: birthDateController,
                readOnly: true,
                decoration: const InputDecoration(
                  hintText: 'Select your birth date',
                  border: OutlineInputBorder(),
                  suffixIcon: Icon(Icons.calendar_today),
                ),
                onTap: () => _pickDate(context, birthDateController, birthDate),
                validator: (value) =>
                    value == null || value.trim().isEmpty ? 'Birth date is required' : null,
              ),
              const SizedBox(height: 16),
              // 11. C.I status label
              const Text('C.I Status', style: TextStyle(fontWeight: FontWeight.bold)),
              // 12. Drop-down list C.I status
              DropdownButtonFormField<String>(
                value: ciStatus,
                decoration: const InputDecoration(
                  border: OutlineInputBorder(),
                  labelText: 'Select C.I status',
                ),
                items: ciStatusOptions
                    .map((status) => DropdownMenuItem(
                          value: status,
                          child: Text(status),
                        ))
                    .toList(),
                onChanged: (value) => setState(() => ciStatus = value),
                validator: (value) =>
                    value == null ? 'C.I status is required' : null,
              ),
              const SizedBox(height: 16),
              // 13. Expedition place of the C.I label
              const Text('Expedition Place of the C.I', style: TextStyle(fontWeight: FontWeight.bold)),
              // 14. Expedition place of the C.I input text
              TextFormField(
                controller: expeditionPlaceController,
                decoration: const InputDecoration(
                  hintText: 'Enter expedition place',
                  border: OutlineInputBorder(),
                ),
                validator: (value) =>
                    value == null || value.trim().isEmpty ? 'Expedition place is required' : null,
              ),
              const SizedBox(height: 16),
              // 15. Date of the expedition C.I label
              const Text('Date of Expedition C.I', style: TextStyle(fontWeight: FontWeight.bold)),
              // 16. Date of the expedition C.I date input
              TextFormField(
                controller: expeditionDateController,
                readOnly: true,
                decoration: const InputDecoration(
                  hintText: 'Select expedition date',
                  border: OutlineInputBorder(),
                  suffixIcon: Icon(Icons.calendar_today),
                ),
                onTap: () => _pickDate(context, expeditionDateController, expeditionDate),
                validator: (value) =>
                    value == null || value.trim().isEmpty ? 'Expedition date is required' : null,
              ),
              const SizedBox(height: 16),
              // 17. Phone number label
              const Text('Phone Number', style: TextStyle(fontWeight: FontWeight.bold)),
              // 18. Phone number input text with Ecuador validation
              TextFormField(
                controller: phoneController,
                decoration: const InputDecoration(
                  hintText: '09XXXXXXXX',
                  border: OutlineInputBorder(),
                ),
                keyboardType: TextInputType.phone,
                maxLength: 10,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Phone number is required';
                  }
                  if (!RegExp(r'^09\d{8}$').hasMatch(value)) {
                    return 'Enter a valid Ecuador phone number';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              // 19. Email label
              const Text('Email', style: TextStyle(fontWeight: FontWeight.bold)),
              // 20. Email input text
              TextFormField(
                controller: emailController,
                decoration: const InputDecoration(
                  hintText: 'Enter your email',
                  border: OutlineInputBorder(),
                ),
                keyboardType: TextInputType.emailAddress,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Email is required';
                  }
                  if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
                    return 'Enter a valid email';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              // 21. Available banks label
              const Text('Available Banks', style: TextStyle(fontWeight: FontWeight.bold)),
              // 22. Drop-down list with list of available banks
              DropdownButtonFormField<String>(
                value: selectedBank,
                decoration: const InputDecoration(
                  border: OutlineInputBorder(),
                  labelText: 'Select bank',
                ),
                items: banks
                    .map((bank) => DropdownMenuItem(
                          value: bank,
                          child: Text(bank),
                        ))
                    .toList(),
                onChanged: (value) => setState(() => selectedBank = value),
                validator: (value) =>
                    value == null ? 'Bank is required' : null,
              ),
              const SizedBox(height: 16),
              // 23. Account type label
              const Text('Account Type', style: TextStyle(fontWeight: FontWeight.bold)),
              // 24. Drop-down list with account types
              DropdownButtonFormField<String>(
                value: accountType,
                decoration: const InputDecoration(
                  border: OutlineInputBorder(),
                  labelText: 'Select account type',
                ),
                items: accountTypes
                    .map((type) => DropdownMenuItem(
                          value: type,
                          child: Text(type),
                        ))
                    .toList(),
                onChanged: (value) => setState(() => accountType = value),
                validator: (value) =>
                    value == null ? 'Account type is required' : null,
              ),
              const SizedBox(height: 16),
              // 25. Account number label
              const Text('Account Number', style: TextStyle(fontWeight: FontWeight.bold)),
              // 26. Account number input text
              TextFormField(
                controller: accountNumberController,
                decoration: const InputDecoration(
                  hintText: 'Enter account number',
                  border: OutlineInputBorder(),
                ),
                keyboardType: TextInputType.number,
                validator: (value) =>
                    value == null || value.trim().isEmpty ? 'Account number is required' : null,
              ),
              const SizedBox(height: 16),
              // 27. Donation amount label
              const Text('Donation Amount', style: TextStyle(fontWeight: FontWeight.bold)),
              // 28. Donation amount input text
              TextFormField(
                controller: donationAmountController,
                decoration: const InputDecoration(
                  hintText: 'e.g. 12.34',
                  border: OutlineInputBorder(),
                ),
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Donation amount is required';
                  }
                  final doubleValue = double.tryParse(value);
                  if (doubleValue == null) return "Enter a valid number";
                  if (!RegExp(r'^\d+(\.\d{1,2})?$').hasMatch(value)) {
                    return "Format must be #.##";
                  }
                  if (doubleValue < 0.00 || doubleValue > 100.00) {
                    return "Amount must be between 0.00 and 100.00";
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              // 29. Donation amount schedule (placeholder)
              const Text('Donation Amount Schedule', style: TextStyle(fontWeight: FontWeight.bold)),
              TextFormField(
                decoration: const InputDecoration(
                  hintText: 'Enter schedule or frequency (optional)',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () {
                  if (_formKey.currentState?.validate() ?? false) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Registration successful!')),
                    );
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFED6F1D),
                  foregroundColor: Colors.white,
                  minimumSize: const Size(180, 48),
                ),
                child: const Text('Register'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}