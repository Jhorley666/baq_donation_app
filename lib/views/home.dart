import 'package:flutter/material.dart';

class HomePage extends StatelessWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context) {
    final _formKey = GlobalKey<FormState>();
    final ciController = TextEditingController();
    final numberController = TextEditingController();
    final passwordController = TextEditingController();

    return Scaffold(
      appBar: AppBar(
        title: const Text('BAQ Donation App'),
      ),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // 1. Logo image at the top
                SizedBox(
                  height: 120,
                  child: Image.asset(
                    'assets/logos/home_logo.png',
                    fit: BoxFit.contain,
                    errorBuilder: (context, error, stackTrace) => const Icon(Icons.image, size: 80),
                  ),
                ),
                const SizedBox(height: 24),
                const Text(
                  'Welcome to BAQ Donation App',
                  style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 32),
                // 2. C.I document input
                TextFormField(
                  controller: ciController,
                  decoration: const InputDecoration(
                    labelText: 'C.I Document',
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
                const SizedBox(height: 16),
                // 3. Number document input
                TextFormField(
                  controller: numberController,
                  decoration: const InputDecoration(
                    labelText: 'Number Document',
                    border: OutlineInputBorder(),
                  ),
                  keyboardType: TextInputType.number,
                  maxLength: 9,
                  validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return 'Number Document is required';
                    }
                    if (!RegExp(r'^\d{9}$').hasMatch(value)) {
                      return 'Must be exactly 9 digits';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                // 4. User password input
                TextFormField(
                  controller: passwordController,
                  decoration: const InputDecoration(
                    labelText: 'Password',
                    border: OutlineInputBorder(),
                  ),
                  obscureText: true,
                  maxLength: 12,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Password is required';
                    }
                    if (value.length < 5) {
                      return 'Password must be at least 5 characters';
                    }
                    if (value.length > 12) {
                      return 'Password must be at most 12 characters';
                    }
                    return null;
                  },
                ),
                // 5. Reset password label
                Align(
                  alignment: Alignment.centerRight,
                  child: TextButton(
                    onPressed: () {
                      Navigator.pushNamed(context, '/reset-password');
                    },
                    child: const Text(
                      'Forgot password?',
                      style: TextStyle(
                        color: Color(0xFFED6F1D),
                        fontWeight: FontWeight.w500,
                        decoration: TextDecoration.underline,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                // 6. Donate button with validation
                ElevatedButton.icon(
                  icon: const Icon(Icons.volunteer_activism),
                  label: const Text('Donate'),
                  onPressed: () async {
                    if (_formKey.currentState?.validate() ?? false) {
                      // TODO: Replace this with your real authentication and role logic
                      // Example: Simulate role selection for demonstration
                      String? userRole = await showDialog<String>(
                        context: context,
                        builder: (context) => AlertDialog(
                          title: const Text('Select Role'),
                          content: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              ElevatedButton(
                                onPressed: () => Navigator.pop(context, 'donator'),
                                child: const Text('Donator'),
                              ),
                              ElevatedButton(
                                onPressed: () => Navigator.pop(context, 'vendor'),
                                child: const Text('Vendor'),
                              ),
                            ],
                          ),
                        ),
                      );

                      if (userRole == 'donator') {
                        Navigator.pushNamed(context, '/donation');
                      } else if (userRole == 'vendor') {
                        Navigator.pushNamed(context, '/qr-generator');
                      } else {
                        // Optionally show an error or do nothing
                      }
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    minimumSize: const Size(180, 48),
                    backgroundColor: const Color(0xFFED6F1D),
                    foregroundColor: Colors.white,
                  ),
                ),
                const SizedBox(height: 16),
                // 7. Registration button with navigation
                ElevatedButton.icon(
                  icon: const Icon(Icons.app_registration),
                  label: const Text('Register'),
                  onPressed: () {
                    Navigator.pushNamed(context, '/register');
                  },
                  style: ElevatedButton.styleFrom(
                    minimumSize: const Size(180, 48),
                    backgroundColor: const Color(0xFFED6F1D),
                    foregroundColor: Colors.white,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}