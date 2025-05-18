import 'package:flutter/material.dart';

class DonateTab extends StatelessWidget {
  final List<String> paymentTypes;
  final String? selectedPayment;
  final bool showOtherImage;
  final Function(String?) onPaymentChanged;

  const DonateTab({
    super.key,
    required this.paymentTypes,
    required this.selectedPayment,
    required this.showOtherImage,
    required this.onPaymentChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        SizedBox(
          height: 120,
          child: Image.asset(
            'assets/logos/donate_logo.png',
            fit: BoxFit.contain,
            errorBuilder: (context, error, stackTrace) =>
                const Icon(Icons.image, size: 80),
          ),
        ),
        const SizedBox(height: 32),
        const Text(
          'Payment type',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
        ),
        const SizedBox(height: 16),
        DropdownButtonFormField<String>(
          value: selectedPayment,
          decoration: const InputDecoration(
            border: OutlineInputBorder(),
            labelText: 'Select payment type',
          ),
          items: paymentTypes
              .map((type) => DropdownMenuItem(
                    value: type,
                    child: Text(type),
                  ))
              .toList(),
          onChanged: onPaymentChanged,
        ),
        const SizedBox(height: 32),
        if (showOtherImage)
          Column(
            children: [
              Image.asset(
                'assets/payment/other_donation.jpeg',
                height: 230,
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) =>
                    const Icon(Icons.image, size: 90),
              ),
              const SizedBox(height: 16),
            ],
          ),
        if (!showOtherImage && selectedPayment != null)
          ElevatedButton(
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Donating with $selectedPayment')),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFED6F1D),
              foregroundColor: Colors.white,
              minimumSize: const Size(180, 48),
            ),
            child: const Text('Donate'),
          ),
      ],
    );
  }
}