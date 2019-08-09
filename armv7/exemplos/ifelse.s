; função principal
main:
; carrega registradores
	MOV R1, #1
	CMP R1, #1
; se R1 != #1 vai o else
	BNE else
	ADD R1, R1, #1
	B endif
else:
	SUB R1, R1, #2
endif:
