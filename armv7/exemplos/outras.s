; A ideia desde código é explorar o uso do CSPR
main:
	MOV R1, #0
	MOV R13, #1025
loop:
; Comparamos R1 com #13
	CMP R1, #13
; Caso não seja somamos 1
	ADDNE R1, R1, #1
; E também ainda caso não seja, voltamos ao looping
	BNE loop
endloop:
; Fazemos algumas operações lógicas
	AND R2, R1, #11
	ORR R3, R1, #11
; Uma subtração invertida
        RSB R4, R3, R2
; E fazemos um teste de conjunção entre R2 e R3
        TST R2, R3
; A conjunção sendo todos os bits 0, executamos a próxima linha
        MULEQ R5, R2, R3
; Não sendo, executamos a linha abaixo
        MULNE R6, R2, R3
; Mais um pouquinho de CSPR
; Comparamos R6 com #100
        CMP R6, #100
; Se R6 > #100, salvamos seu valor na posição de memória R13 - 1
        STRGT R6, [R13, #-1]
; Vamos terminar com um XOR
        EOR R12, R13, #1023
